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
use App\Model\RequestModel;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Form\Form;
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
    /**
     * Deals with replies to messages and hosting requests.
     *
     * @Route("/request/{id}/reply", name="hosting_request_reply",
     *     requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param Message $message
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function replyToHostingRequestAction(Request $request, Message $message)
    {
        $sender = $this->getUser();
        if (($message->getReceiver() !== $sender) && ($message->getSender() !== $sender)) {
            throw new AccessDeniedException();
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        // Forward to MessageController in case something got mixed-up
        $isMessage = (null === $message->getRequest()) ? true : false;
        if ($isMessage) {
            return $this->redirectToRoute('message_reply', ['id' => $current->getId()]);
        }

        // Always reply to the last item in the thread
        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('hosting_request_reply', ['id' => $current->getId()]);
        }

        // determine if guest or host reply to a request
        $first = $thread[\count($thread) - 1];
        if ($sender->getId() === $first->getSender()->getId()) {
            return $this->hostingRequestGuestReply($request, $thread);
        }

        return $this->hostingRequestHostReply($request, $thread);
    }

    /**
     * @Route("/request/{id}", name="hosting_request_show",
     *     requirements={"id": "\d+"})
     *
     * @param Message $message
     *
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function show(Message $message)
    {
        $member = $this->getUser();
        if (($message->getReceiver() !== $member) && ($message->getSender() !== $member)) {
            throw new AccessDeniedException();
        }

        $isMessage = (null === $message->getRequest()) ? true : false;

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($isMessage) {
            return $this->redirectToRoute('message_show', ['id' => $current->getId()]);
        }

        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('hosting_request_show', ['id' => $current->getId()]);
        }

        // Walk through the thread and mark all messages as read (for current member)
        $em = $this->getDoctrine()->getManager();
        foreach ($thread as $item) {
            if ($member === $item->getReceiver()) {
                // Only mark as read if it is a message and when the receiver reads the message,
                // not when the message is presented to the Sender with url /messages/{id}/sent
                $item->setWhenFirstRead(new \DateTime());
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

        $requestForm = $this->createForm(HostingRequestGuest::class);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            // Write request to database after doing some checks
            /** @var Message $hostingRequest */
            $guest = $this->getUser();
            $hostingRequest = $requestForm->getData();
            $hostingRequest->setSender($guest);
            $hostingRequest->setReceiver($host);
            $hostingRequest->setWhenFirstRead(null);
            $hostingRequest->setStatus('Sent');
            $hostingRequest->setInfolder('Normal');
            $hostingRequest->setCreated(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();

            $success = $this->sendInitialRequestNotification(
                $host,
                $guest,
                $hostingRequest
            );
            if ($success) {
                $this->addTranslatedFlash('success', 'flash.request.sent');
            } else {
                $this->addTranslatedFlash('notice', 'flash.request.stored');
            }

            return $this->redirectToRoute('members_profile', ['username' => $host->getUsername()]);
        }

        return $this->render('request/request.html.twig', [
            'receiver' => $host,
            'form' => $requestForm->createView(),
        ]);
    }

    /**
     * @Route("/requests/{folder}", name="requests",
     *     defaults={"folder": "inbox"})
     *
     * @param Request $request
     * @param string  $folder
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function requests(Request $request, $folder)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'datesent');
        $sortDir = $request->query->get('dir', 'desc');

        if (!\in_array($sortDir, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException();
        }

        $member = $this->getUser();
        $messages = $this->messageModel->getFilteredRequests($member, $folder, $sort, $sortDir, $page, $limit);

        return $this->handleFolderRequest($request, $folder, $messages, 'requests');
    }

    protected function sendRequestNotification(Member $sender, Member $receiver, Message $request, $subject, $template)
    {
        // Send mail notification with the receiver's preferred locale
        $this->setTranslatorLocale($receiver);

        $body = $this->renderView($template, [
            'sender' => $sender,
            'receiver' => $receiver,
            'subject' => $subject,
            'message' => $request,
            'request' => $request->getRequest(),
        ]);

        // Reset to former locale as otherwise flash notification will be shown in receiver's locale
        $this->setTranslatorLocale($sender);

        return $this->sendEmail($sender, $receiver, $subject, $body);
    }

    /**
     * @param Member  $host
     * @param Member  $guest
     * @param Message $request
     *
     * @return bool
     */
    protected function sendInitialRequestNotification(Member $host, Member $guest, Message $request)
    {
        $subject = $request->getSubject()->getSubject();

        return $this->sendRequestNotification($guest, $host, $request, $subject, 'emails/request.html.twig');
    }

    /**
     * @param Member  $guest
     * @param Member  $host
     * @param Message $request
     * @param mixed   $subject
     *
     * @return bool
     */
    protected function sendHostReplyNotification(Member $host, Member $guest, Message $request, $subject)
    {
        return $this->sendRequestNotification($host, $guest, $request, $subject, 'emails/reply_host.html.twig');
    }

    /**
     * @param Member  $guest
     * @param Member  $host
     * @param Message $request
     * @param mixed   $subject
     *
     * @return bool
     */
    protected function sendGuestReplyNotification(Member $host, Member $guest, Message $request, $subject)
    {
        return $this->sendRequestNotification($guest, $host, $request, $subject, 'emails/reply_guest.html.twig');
    }

    protected function checkRequestExpired(HostingRequest $request)
    {
        $requestModel = new RequestModel();

        return $requestModel->checkRequestExpired($request);
    }

    /**
     * @param Request       $request
     * @param array Message $thread
     *
     * @throws InvalidArgumentException
     *
     * @return Response
     */
    private function hostingRequestGuestReply(Request $request, array $thread)
    {
        $hostingRequest = $thread[0];
        if (null === $hostingRequest->getRequest()) {
            // This should never happen as it is handled in replyToMessageOrHostingRequest
            //so we throw an exception in this case
            throw new InvalidArgumentException('wrong call to hosting reply guest');
        }

        $user = $this->getUser();
        /** @var Message $first */
        $first = $thread[\count($thread) - 1];
        $guest = $first->getSender();
        $host = $first->getReceiver();

        if ($user->getId() === $host->getId()) {
            // This should never happen so we throw an exception in this case
            throw new InvalidArgumentException('wrong call to hosting reply guest');
        }

        if ($user->getId() !== $guest->getId()) {
            // This should never happen so we throw an exception in this case
            throw new InvalidArgumentException('wrong call to hosting reply guest');
        }

        if ($this->checkRequestExpired($hostingRequest->getRequest())) {
            $this->addExpiredFlash($host);

            return $this->redirectToRoute('hosting_request_show', ['id' => $hostingRequest->getId()]);
        }

        // A reply consists of a new message and maybe a change of the status of the hosting request
        // Additionally the user might change the dates of the request or cancel the request altogether
        $requestForm = $this->createForm(HostingRequestGuest::class, $hostingRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $requestForm->getData();
            $clickedButton = $requestForm->getClickedButton()->getName();

            // handle changes in request and subject
            $newRequest = $this->getFinalRequest($guest, $host, $hostingRequest, $data, $clickedButton);
            $newRequest->setWhenFirstRead(null);
            $newRequest->setStatus(MessageStatusType::SENT);
            $em->persist($newRequest);
            $em->flush();

            $subject = $newRequest->getSubject()->getSubject();
            if ('Re:' !== substr($subject, 0, 3)) {
                $subject = 'Re: '.$subject;
            }

            if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
                if (false === strpos('(Cancelled)', $subject)) {
                    $subject = $subject.' (Cancelled)';
                }
            }

            $this->sendGuestReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject
            );
            $this->addTranslatedFlash('success', 'flash.notification.updated');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        $thread = $this->messageModel->getThreadForMessage($hostingRequest);

        return $this->render('request/reply_guest.html.twig', [
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @param Request       $request
     * @param array Message $thread
     *
     * @throws InvalidArgumentException
     *
     * @return Response
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * Ignore as too strict in this case (function is easily readable)
     */
    private function hostingRequestHostReply(Request $request, array $thread)
    {
        $hostingRequest = $thread[0];

        $user = $this->getUser();
        $first = $thread[\count($thread) - 1];
        /** @var Member $guest */
        $guest = $first->getSender();
        /** @var Member $host */
        $host = $first->getReceiver();

        if ($user->getId() === $guest->getId()) {
            // This should never happen as it is handled in replyToMessageOrHostingRequest
            //so we throw an exception in this case
            throw new InvalidArgumentException('wrong call to hosting reply guest');
        }

        if ($user->getId() !== $host->getId()) {
            // This should never happen as it is handled in replyToMessageOrHostingRequest
            //so we throw an exception in this case
            throw new InvalidArgumentException('wrong call to hosting reply guest');
        }

        if ($this->checkRequestExpired($hostingRequest->getRequest())) {
            $this->addExpiredFlash($guest);

            return $this->redirectToRoute('hosting_request_show', ['id' => $hostingRequest->getId()]);
        }

        /** @var Form $requestForm */
        $requestForm = $this->createForm(HostingRequestHost::class, $hostingRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $requestForm->getData();
            $clickedButton = $requestForm->getClickedButton()->getName();
            $newRequest = $this->getFinalRequest($host, $guest, $hostingRequest, $data, $clickedButton);
            $newRequest->setWhenFirstRead(null);
            $newRequest->setStatus(MessageStatusType::SENT);
            $em->persist($newRequest);

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();

            $subject = $newRequest->getSubject()->getSubject();
            if ('Re:' !== substr($subject, 0, 3)) {
                $subject = 'Re: '.$subject;
            }

            if (HostingRequest::REQUEST_DECLINED === $newRequest->getRequest()->getStatus() && (false === strpos('(Declined)', $subject))) {
                $subject = $subject.' (Declined)';
            }

            if (HostingRequest::REQUEST_ACCEPTED === $newRequest->getRequest()->getStatus() &&
                 (false === strpos('(Accepted)', $subject))) {
                $subject = $subject.' (Accepted)';
            }

            if (HostingRequest::REQUEST_TENTATIVELY_ACCEPTED === $newRequest->getRequest()->getStatus() &&
                (false === strpos('(Tentatively accepted)', $subject))) {
                $subject = $subject.' (Tentatively accepted)';
            }

            $this->sendHostReplyNotification(
                $host,
                $guest,
                $newRequest,
                $subject
            );
            $this->addTranslatedFlash('notice', 'flash.notification.updated');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        $thread = $this->messageModel->getThreadForMessage($hostingRequest);

        return $this->render('request/reply_host.html.twig', [
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @param Message $hostingRequest
     * @param Message $data
     * @param $clickedButton
     * @param mixed $sender
     * @param mixed $receiver
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return Message
     */
    private function getFinalRequest(Member $sender, Member $receiver, Message $hostingRequest, Message $data, $clickedButton)
    {
        $finalRequest = new Message();
        $finalRequest->setSender($sender);
        $finalRequest->setReceiver($receiver);
        $finalRequest->setParent($hostingRequest->getParent());
        $finalRequest->setSubject($hostingRequest->getSubject());
        $finalRequest->setRequest($hostingRequest->getRequest());

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
        if ($newStateSet || $newArrival || $newDeparture || $newFlexible || $newNumberOfTravellers) {
            $newHostingRequest = new HostingRequest();
            $newHostingRequest->setStatus($newState);
            $newHostingRequest->setArrival($data->getRequest()->getArrival());
            $newHostingRequest->setDeparture($data->getRequest()->getDeparture());
            $newHostingRequest->setFlexible($data->getRequest()->getFlexible());
            $newHostingRequest->setNumberOfTravellers($data->getRequest()->getNumberOfTravellers());
            $finalRequest->setRequest($newHostingRequest);
        }

        return $finalRequest;
    }
}
