<?php

namespace App\Controller;

use App\Doctrine\AccommodationType;
use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Form\HostingRequestGuest;
use App\Form\HostingRequestHost;
use App\Model\HostingRequestModel;
use App\Utilities\MailerTrait;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class HostingRequestController.
 *
 * Ignore complexity warning. \todo fix this.
 *
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class HostingRequestController extends BaseMessageController
{
    use MailerTrait;
    use ManagerTrait;
    use TranslatorTrait;

    /**
     * Deals with replies to messages and hosting requests.
     *
     * @Route("/request/{id}/reply", name="hosting_request_reply",
     *     requirements={"id": "\d+"})
     *
     * @param Message $message
     *
     * @throws AccessDeniedException
     *
     * @return RedirectResponse
     */
    public function replyToHostingRequestAction(Message $message)
    {
        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        if ($this->isMessage($message)) {
            return $this->redirectToMessageReply($message);
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        // Always reply to the last item in the thread
        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('hosting_request_reply', ['id' => $current->getId()]);
        }

        // determine if guest or host reply to a request
        $member = $this->getUser();
        $first = $thread[\count($thread) - 1];
        $parentId = ($message->getParent()) ? $message->getParent()->getId() : $message->getId();
        if ($member === $first->getSender()) {
            return $this->redirectToRoute('hosting_request_reply_guest', [
                'id' => $message->getId(),
                'parentId' => $parentId,
            ]);
        }

        return $this->redirectToRoute('hosting_request_reply_host', [
            'id' => $message->getId(),
            'parentId' => $parentId,
        ]);
    }

    /**
     * @Route("/request/{id}/reply/guest/{parentId}", name="hosting_request_reply_guest",
     *     requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param Message $hostingRequest
     *
     * @ParamConverter("parent", class="App\Entity\Message", options={"id": "parentId"})
     *
     * @param Message $parent
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function hostingRequestGuestReply(Request $request, Message $hostingRequest, Message $parent)
    {
        /** @var Message $first */
        /** @var Message $last */
        /** @var Member $guest */
        /** @var Member $host */
        list($thread, $first, $last, $guest, $host) = $this->messageModel->getThreadInformationForMessage($hostingRequest);

        if ($this->checkRequestExpired($last)) {
            $this->addExpiredFlash($host);

            return $this->redirectToRoute('hosting_request_show', ['id' => $last->getId()]);
        }

        // keep all information from current hosting request except the message text
        $hostingRequest = $this->getRequestClone($last);

        // A reply consists of a new message and maybe a change of the status of the hosting request
        // Additionally the user might change the dates of the request or cancel the request altogether
        /** @var Form $requestForm */
        $requestForm = $this->createForm(HostingRequestGuest::class, $hostingRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->getParent($parent);

            /** @var Message $newRequest */
            $newRequest = $this->persistRequest($requestForm, $guest, $host, $realParent);

            $subject = $this->getSubjectForReply($newRequest);

            $this->sendGuestReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject,
                ($newRequest->getRequest()->getId() !== $realParent->getRequest()->getId())
            );
            $this->addTranslatedFlash('success', 'flash.notification.updated');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        return $this->render('request/reply_from_guest.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/request/{id}/reply/host/{parentId}", name="hosting_request_reply_host",
     *     requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param Message $hostingRequest
     *
     * @ParamConverter("parent", class="App\Entity\Message", options={"id": "parentId"})
     *
     * @param Message $parent
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function hostingRequestHostReply(Request $request, Message $hostingRequest, Message $parent)
    {
        /** @var Message $first */
        /** @var Message $last */
        /** @var Member $guest */
        /** @var Member $host */
        list($thread, $first, $last, $guest, $host) = $this->messageModel->getThreadInformationForMessage($hostingRequest);

        if ($this->checkRequestExpired($last)) {
            $this->addExpiredFlash($guest);

            return $this->redirectToRoute('hosting_request_show', ['id' => $last->getId()]);
        }

        // keep all information from current hosting request except the message text
        $hostingRequest = $this->getRequestClone($last);

        /** @var Form $requestForm */
        $requestForm = $this->createForm(HostingRequestHost::class, $hostingRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $realParent = $this->getParent($parent);

            /** @var Message $newRequest */
            $newRequest = $this->persistRequest($requestForm, $host, $guest, $realParent);

            $subject = $this->getSubjectForReply($newRequest);

            $this->sendHostReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject,
                ($newRequest->getRequest()->getId() !== $realParent->getRequest()->getId())
            );
            $this->addTranslatedFlash('notice', 'flash.notification.updated');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        return $this->render('request/reply_from_host.html.twig', [
            'guest' => $guest,
            'host' => $host,
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/request/{id}", name="hosting_request_show",
     *     requirements={"id": "\d+"})
     *
     * @param Message $message
     *
     * @throws AccessDeniedException
     * @throws Exception
     *
     * @return Response
     */
    public function show(Message $message)
    {
        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message');
        }

        if ($this->isMessage($message)) {
            return $this->redirectToMessage($message);
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('hosting_request_show', ['id' => $current->getId()]);
        }

        // Walk through the thread and mark all messages as read (for current member)
        $member = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        foreach ($thread as $item) {
            if ($member === $item->getReceiver()) {
                // Only mark as read if it is a message and when the receiver reads the message,
                // not when the message is presented to the Sender with url /messages/{id}/sent
                $item->setFirstRead(new \DateTime());
                $em->persist($item);
            }
        }
        $em->flush();

        return $this->render('request/view.html.twig', [
            'current' => $current,
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/new/request/{username}", name="hosting_request")
     *
     * @param Request $request
     * @param Member  $host
     *
     * @throws Exception
     *
     * @return Response
     */
    public function newHostingRequestAction(Request $request, Member $host)
    {
        /** @var Member $member */
        $member = $this->getUser();
        if ($member === $host) {
            $this->addTranslatedFlash('notice', 'flash.request.self');

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        if (!$host->isBrowseable()) {
            $this->addTranslatedFlash('note', 'flash.member.invalid');
        }

        if ($this->messageModel->hasMessageLimitExceeded(
            $member,
            $this->getParameter('new_members_messages_per_hour'),
            $this->getParameter('new_members_messages_per_day')
        )) {
            $this->addTranslatedFlash('error', 'flash.request.limit');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        if (AccommodationType::NO === $host->getAccommodation()) {
            $this->addTranslatedFlash('notice', 'request.not.hosting');

            return $this->redirectToRoute('members_profile', ['username' => $host->getUsername()]);
        }

        $requestForm = $this->createForm(HostingRequestGuest::class, null);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            // Write request to database after doing some checks
            /** @var Message $hostingRequest */
            $hostingRequest = $requestForm->getData();
            $hostingRequest->setSender($member);
            $hostingRequest->setReceiver($host);
            $hostingRequest->setFirstRead(null);
            $hostingRequest->setStatus('Sent');
            $hostingRequest->setFolder('Normal');
            $hostingRequest->setCreated(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();

            $this->sendInitialRequestNotification(
                $host,
                $member,
                $hostingRequest
            );
            $this->addTranslatedFlash('success', 'flash.request.sent');

            return $this->redirectToRoute('members_profile', ['username' => $host->getUsername()]);
        }

        return $this->render('request/request.html.twig', [
            'guest' => $member,
            'host' => $host,
            'form' => $requestForm->createView(),
        ]);
    }

    /**
     * @Route("/requests/{folder}", name="requests",
     *     defaults={"folder": "inbox"})
     *
     * @param Request $request
     * @param string $folder
     *
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function requests(Request $request, $folder)
    {
        list($page, $limit, $sort, $direction) = $this->getOptionsFromRequest($request);

        if (!\in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException();
        }

        $member = $this->getUser();
        $messages = $this->messageModel->getFilteredRequests($member, $folder, $sort, $direction, $page, $limit);

        return $this->handleFolderRequest($request, $folder, $messages, 'requests');
    }

    /**
     * @param Member $host
     * @param Member $guest
     * @param Message $request
     * @param mixed $subject
     * @param $requestChanged
     */
    protected function sendGuestReplyNotification(Member $host, Member $guest, Message $request, $subject, $requestChanged)
    {
        $this->sendRequestNotification($guest, $host, $host, $request, $subject, 'reply_from_guest', $requestChanged);
    }

    /**
     * @param Message $hostingRequest
     *
     * @return bool
     */
    protected function checkRequestExpired(Message $hostingRequest)
    {
        $requestModel = new HostingRequestModel();

        return $requestModel->checkRequestExpired($hostingRequest->getRequest());
    }

    private function sendRequestNotification(
        Member $sender,
        Member $receiver,
        Member $host,
        Message $request,
        $subject,
        $template,
        $requestChanged = false
    ) {
        // Send mail notification
        $this->sendTemplateEmail($sender, $receiver, $template, [
            'host' => $host,
            'subject' => $subject,
            'message' => $request,
            'request' => $request->getRequest(),
            'changed' => $requestChanged,
        ]);

        return true;
    }

    /**
     * @param Member  $host
     * @param Member  $guest
     * @param Message $request
     */
    private function sendInitialRequestNotification(Member $host, Member $guest, Message $request)
    {
        $subject = $request->getSubject()->getSubject();

        $this->sendRequestNotification($guest, $host, $host, $request, $subject, 'request');
    }

    /**
     * @param Member  $guest
     * @param Member  $host
     * @param Message $request
     * @param mixed   $subject
     */
    private function sendHostReplyNotification(Member $host, Member $guest, Message $request, $subject, $requestChanged)
    {
        $this->sendRequestNotification($host, $guest, $host, $request, $subject, 'reply_from_host', $requestChanged);
    }

    /**
     * @param Message $hostingRequest
     * @param Message $data
     * @param $clickedButton
     * @param mixed $sender
     * @param mixed $receiver
     *
     * @return Message
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @throws InvalidArgumentException
     */
    private function getFinalRequest(Member $sender, Member $receiver, Message $hostingRequest, Message $data, $clickedButton)
    {
        $finalRequest = new Message();
        $finalRequest->setSender($sender);
        $finalRequest->setReceiver($receiver);
        $finalRequest->setParent($hostingRequest);
        $finalRequest->setMessage($data->getMessage());
        $finalRequest->setSubject($hostingRequest->getSubject());
        $finalRequest->setStatus(MessageStatusType::SENT);

        $oldState = $hostingRequest->getRequest()->getStatus();
        $newState = $oldState;
        switch ($clickedButton) {
            case 'cancel':
                $newState = HostingRequest::REQUEST_CANCELLED;
                break;
            case 'decline':
                $newState = HostingRequest::REQUEST_DECLINED;
                break;
            case 'tentatively':
                $newState = HostingRequest::REQUEST_TENTATIVELY_ACCEPTED;
                break;
            case 'accept':
                $newState = HostingRequest::REQUEST_ACCEPTED;
                break;
        }

        $newStateSet = ($oldState !== $newState);

        // check if request was altered
        $diff = date_diff($data->getRequest()->getArrival(), $hostingRequest->getRequest()->getArrival());
        $newArrival = (0 !== $diff->y) || (0 !== $diff->m) || (0 !== $diff->d);
        if (null !== $data->getRequest()->getDeparture() && null !== $hostingRequest->getRequest()->getDeparture()) {
            $diff = date_diff($data->getRequest()->getDeparture(), $hostingRequest->getRequest()->getDeparture());
            $newDeparture = (0 !== $diff->y) || (0 !== $diff->m) || (0 !== $diff->d);
        } else {
            // departure date was either set or removed so we set newDeparture to true
            $newDeparture = true;
        }
        $newFlexible = ($data->getRequest()->getFlexible() !== $hostingRequest->getRequest()->getFlexible());
        $newNumberOfTravellers = ($data->getRequest()->getNumberOfTravellers()
            !== $hostingRequest->getRequest()->getNumberOfTravellers());
        if ($newArrival || $newDeparture || $newFlexible || $newNumberOfTravellers) {
            $newHostingRequest = new HostingRequest();
            $newHostingRequest->setStatus($newState);
            $newHostingRequest->setArrival($data->getRequest()->getArrival());
            $newHostingRequest->setDeparture($data->getRequest()->getDeparture());
            $newHostingRequest->setFlexible($data->getRequest()->getFlexible());
            $newHostingRequest->setNumberOfTravellers($data->getRequest()->getNumberOfTravellers());
            $finalRequest->setRequest($newHostingRequest);
        } else {
            $finalRequest->setRequest($hostingRequest->getRequest());
        }
        if ($newStateSet) {
            $finalRequest->getRequest()->setStatus($newState);
        }

        return $finalRequest;
    }

    private function addExpiredFlash(Member $receiver)
    {
        $this->addTranslatedFlash('notice', 'flash.request.expired', [
            '%link_start%' => '<a href="'.$this->generateUrl('message_new', [
                    'username' => $receiver->getUsername(),
                ]).'" class="text-primary">',
            '%link_end%' => '</a>',
        ]);
    }

    private function getRequestClone(Message $hostingRequest)
    {
        // copy only the bare minimum needed
        $newRequest = new Message();
        $newRequest->setSubject($hostingRequest->getSubject());
        $newHostingRequest = clone $hostingRequest->getRequest();
        $newRequest->setRequest($newHostingRequest);
        $newRequest->setMessage('');

        return $newRequest;
    }

    private function persistRequest(Form $requestForm, Member $sender, Member $receiver, $currentRequest)
    {
        $data = $requestForm->getData();
        $em = $this->getDoctrine()->getManager();
        $clickedButton = $requestForm->getClickedButton()->getName();

        // handle changes in request and subject
        $newRequest = $this->getFinalRequest($sender, $receiver, $currentRequest, $data, $clickedButton);
        $em->persist($newRequest);
        $em->flush();

        return $newRequest;
    }

    private function getSubjectForReply(Message $newRequest)
    {
        $subject = $newRequest->getSubject()->getSubject();
        if ('Re:' !== substr($subject, 0, 3)) {
            $subject = 'Re: '.$subject;
        }

        if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
            if (false === strpos('(Cancelled)', $subject)) {
                $subject = $subject.' (Cancelled)';
            }
        }

        if (HostingRequest::REQUEST_DECLINED === $newRequest->getRequest()->getStatus()) {
            if (false === strpos('(Declined)', $subject)) {
                $subject = $subject.' (Declined)';
            }
        }

        if (HostingRequest::REQUEST_ACCEPTED === $newRequest->getRequest()->getStatus()) {
            if (false === strpos('(Accepted)', $subject)) {
                $subject = $subject.' (Accepted)';
            }
        }

        if (HostingRequest::REQUEST_TENTATIVELY_ACCEPTED === $newRequest->getRequest()->getStatus()) {
            if (false === strpos('(Tentatively accepted)', $subject)) {
                $subject = $subject.' (Tentatively accepted)';
            }
        }

        return $subject;
    }

    private function isMessage(Message $message)
    {
        return (null === $message->getRequest()) ? true : false;
    }

    private function redirectToMessage(Message $message)
    {
        return $this->redirectToRoute('message_show', ['id' => $message->getId()]);
    }

    private function redirectToMessageReply(Message $message)
    {
        return $this->redirectToRoute('message_reply', ['id' => $message->getId()]);
    }
}
