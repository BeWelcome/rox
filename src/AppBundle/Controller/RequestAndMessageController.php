<?php

namespace AppBundle\Controller;

use AppBundle\Entity\HostingRequest;
use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use AppBundle\Entity\Subject;
use AppBundle\Form\HostingRequestGuest;
use AppBundle\Form\HostingRequestHost;
use AppBundle\Form\MessageToMemberType;
use AppBundle\Model\MessageModel;
use AppBundle\Model\RequestModel;
use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Html2Text\Html2Text;
use Rox\Core\Exception\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class RequestAndMessageController.
 *
 * Ignore complexity warning. \todo fix this.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class RequestAndMessageController extends Controller
{
    /**
     * Deals with replies to messages and hosting requests.
     *
     * @Route("/message/{id}/reply", name="message_reply",
     *     requirements={"id": "\d+"})
     * @Route("/request/{id}/reply", name="hosting_request_reply",
     *     requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param Message $message
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return Response
     */
    public function replyToMessageOrHostingRequestAction(Request $request, Message $message)
    {
        $isMessage = (null === $message->getRequest()) ? true : false;

        $sender = $this->getUser();
        if (($message->getReceiver() !== $sender) && ($message->getSender() !== $sender)) {
            throw new AccessDeniedException();
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            if ($isMessage) {
                return $this->redirectToRoute('message_reply', ['id' => $current->getId()]);
            }

            return $this->redirectToRoute('hosting_request_reply', ['id' => $current->getId()]);
        }

        // Always reply to last message in thread (sorted descending!)
        if ($isMessage) {
            return $this->messageReply($request, $sender, $thread);
        }

        // determine if guest or host reply to a request
        $first = $thread[count($thread) - 1];
        if ($sender->getId() === $first->getSender()->getId()) {
            return $this->hostingRequestGuestReply($request, $thread);
        }

        return $this->hostingRequestHostReply($request, $thread);
    }

    /**
     * @Route("/message/{id}", name="message_show",
     *     requirements={"id": "\d+"})
     * @Route("/request/{id}", name="hosting_request_show",
     *     requirements={"id": "\d+"})
     *
     * @param Message $message
     *
     * @throws \Doctrine\DBAL\DBALException
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

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            if ($isMessage) {
                return $this->redirectToRoute('message_show', ['id' => $current->getId()]);
            }

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

        $view = (null === $message->getRequest()) ? ':message:view.html.twig' : ':request:view.html.twig';

        return $this->render($view, [
            'current' => $current,
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/new/message/{username}", name="message_new")
     *
     * @param Request $request
     * @param Member  $receiver
     *
     * @return Response
     */
    public function newMessageAction(Request $request, Member $receiver)
    {
        $messageForm = $this->createForm(MessageToMemberType::class);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            // Write request to database after doing some checks
            $sender = $this->getUser();
            $message = $messageForm->getData();
            $message->setSender($sender);
            $message->setReceiver($receiver);
            $message->setInfolder('Normal');
            $message->setCreated(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();

            $success = $this->sendMailNotification(
                $sender,
                $receiver,
                $message->getSubject()->getSubject(),
                $message->getMessage(),
                'message'
            );
            if ($success) {
                $this->addFlash('success', 'Request has been sent.');
            } else {
                $this->addFlash('notice', 'Request has been stored into the database. Mail notification couldn\'t be sent, though.');
            }

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render(':message:message.html.twig', [
            'receiver' => $receiver,
            'form' => $messageForm->createView(),
        ]);
    }

    /**
     * @Route("/new/request/{username}", name="hosting_request")
     *
     * @param Request $request
     * @param Member  $receiver
     *
     * @return Response
     */
    public function newHostingRequestAction(Request $request, Member $receiver)
    {
        $member = $this->getUser();
        if ($member === $receiver) {
            $this->addFlash('notice', 'You can\'t send yourself a hosting request.');

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        if (Member::ACC_NO === $receiver->getAccommodation()) {
            $this->addFlash('notice', 'This person says they are not willing to host.<hr>You might send a message instead.');

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        $requestForm = $this->createForm(HostingRequestGuest::class);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            // Write request to database after doing some checks
            /** @var Message $hostingRequest */
            $sender = $this->getUser();
            $hostingRequest = $requestForm->getData();
            $hostingRequest->setSender($sender);
            $hostingRequest->setReceiver($receiver);
            $hostingRequest->setWhenFirstRead(new DateTime('0000-00-00 00:00:00'));
            $hostingRequest->setStatus('Sent');
            $hostingRequest->setInfolder('requests');
            $hostingRequest->setCreated(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();

            $success = $this->sendMailNotification(
                $sender,
                $receiver,
                $hostingRequest->getSubject()->getSubject(),
                $hostingRequest->getMessage(),
                'request'
            );
            if ($success) {
                $this->addFlash('success', 'Request has been sent.');
            } else {
                $this->addFlash('notice', 'Request has been stored into the database. Mail notification couldn\'t be sent, though.');
            }

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render(':request:request.html.twig', [
            'receiver' => $receiver,
            'form' => $requestForm->createView(),
        ]);
    }

    /**
     * @Route("/messages/{folder}", name="messages",
     *     defaults={"folder": "inbox"})
     * @Route("/requests/{folder}", name="requests",
     *     defaults={"folder": "inbox"})
     * @Route("/both/{folder}", name="both",
     *     defaults={"folder": "inbox"})
     *
     * @param Request $request
     * @param string  $folder
     *
     * @return Response
     */
    public function folders(Request $request, $folder)
    {
        $url = $request->getPathInfo();
        if ('/' !== $url[-1]) {
            $url .= '/';
        }
        preg_match('#/(.+?)/#', $url, $matches);
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'datesent');
        $sortDir = $request->query->get('dir', 'desc');

        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException();
        }

        $member = $this->getUser();

        $messageModel = new MessageModel($this->getDoctrine());
        $messages = $messageModel->getFilteredMessages($member, $matches[1], $folder, $sort, $sortDir, $page, $limit);

        return $this->render(':message:index.html.twig', [
            'items' => $messages,
            'type' => 'UserMessages',
            'folder' => $folder,
            'filter' => $request->query->all(),
            'submenu' => [
                'active' => $matches[1].'_'.$folder,
                'items' => $this->getSubMenuItems(),
            ],
        ]);
    }

    /**
     * Takes care of the reply to a message.
     *
     * @param Request   $request
     * @param Member    $sender
     * @param Message[] $thread
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    private function messageReply(Request $request, Member $sender, array $thread)
    {
        $message = $thread[0];
        $receiver = ($message->getReceiver() === $sender) ? $message->getSender() : $message->getReceiver();

        $replyMessage = new Message();
        $replySubject = new Subject();
        $subject = $message->getSubject();
        if (null !== $subject) {
            $replySubject->setSubject($subject->getSubject());
            $replyMessage->setSubject($replySubject);
        }

        $messageForm = $this->createForm(MessageToMemberType::class, $replyMessage);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $replyMessage = $messageForm->getData();
            $replyMessage->setParent($message);
            $replyMessage->setSender($sender);
            $replyMessage->setReceiver($receiver);
            $replyMessage->setInfolder('Normal');
            $replyMessage->setCreated(new \DateTime());

            $replySubject = $replyMessage->getSubject()->getSubject();
            if (null !== $subject && $subject->getSubject() === $replySubject) {
                $replyMessage->setSubject($subject);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($replyMessage);
            $em->flush();

            // $replyMessage->refresh();
            return $this->redirectToRoute('message_show', ['id' => $replyMessage->getId()]);
        }

        return $this->render(':message:reply.html.twig', [
            'form' => $messageForm->createView(),
            'current' => $message,
            'thread' => $thread,
        ]);
    }

    /**
     * @param Request       $request
     * @param array Message $thread
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * Ignore as too strict in this case (function is easily readable)
     *
     * @throws \Doctrine\DBAL\DBALException
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
        $first = $thread[count($thread) - 1];
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
            $this->addFlash('notice', 'This request can\'t be replied to anymore as the hosting period already started.');

            return $this->redirectToRoute('hosting_request_show', ['id' => $hostingRequest->getId()]);
        }

        // A reply consists of a new message and maybe a change of the status of the hosting request
        // Additionally the user might change the dates of the request or cancel the request altogether
        $newRequest = new Message();
        $newRequest->setSender($guest);
        $newRequest->setReceiver($host);
        $newRequest->setRequest($hostingRequest->getRequest());
        $newRequest->setSubject($hostingRequest->getSubject());

        $requestForm = $this->createForm(HostingRequestGuest::class, $newRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $requestForm->getData();
            $clickedButton = $requestForm->getClickedButton()->getName();

            // handle changes in request and subject
            $newRequest = $this->getFinalRequest($em, $newRequest, $hostingRequest, $data, $clickedButton);
            $em->persist($newRequest);
            $em->flush();

            $subject = $newRequest->getSubject()->getSubject();
            if ('Re:' !== substr($subject, 0, 3)) {
                $subject = 'Re: '.$subject;
            }

            if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
                $subject = 'Canceled: '.$subject;
            }

            $this->sendMailNotification(
                $guest,
                $host,
                $subject,
                $newRequest->getMessage(),
                'request'
            );
            $this->addFlash('success', 'Notification with updated information has been sent.');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($hostingRequest);

        return $this->render(':request:reply_guest.html.twig', [
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @param Request       $request
     * @param array Message $thread
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * Ignore as too strict in this case (function is easily readable)
     *
     * @return Response
     */
    private function hostingRequestHostReply(Request $request, array $thread)
    {
        $hostingRequest = $thread[0];

        $user = $this->getUser();
        $first = $thread[count($thread) - 1];
        $guest = $first->getSender();
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
            $this->addFlash('notice', 'This request can\'t be replied to anymore as the hosting period already started.');

            return $this->redirectToRoute('hosting_request_show', ['id' => $hostingRequest->getId()]);
        }

        // A reply consists of a new message and maybe a change of the status of the hosting request
        // Additionally the user might change the dates of the request or cancel the request altogether
        $newRequest = new Message();
        $newRequest->setSender($host);
        $newRequest->setReceiver($guest);
        $newRequest->setRequest($hostingRequest->getRequest());
        $newRequest->setSubject($hostingRequest->getSubject());

        $requestForm = $this->createForm(HostingRequestHost::class, $newRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $requestForm->getData();
            $clickedButton = $requestForm->getClickedButton()->getName();
            $newRequest = $this->getFinalRequest($em, $newRequest, $hostingRequest, $data, $clickedButton);
            $em->persist($newRequest);

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();

            $subject = $newRequest->getSubject()->getSubject();
            if ('Re:' !== substr($subject, 0, 3)) {
                $subject = 'Re: '.$subject;
            }

            if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
                $subject = 'Canceled: '.$subject;
            }

            $this->sendMailNotification(
                $guest,
                $host,
                $subject,
                $newRequest->getMessage(),
                'request'
            );
            $this->addFlash('notice', 'Notification with updated information has been sent.');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($hostingRequest);

        return $this->render(':request:reply_host.html.twig', [
            'form' => $requestForm->createView(),
            'thread' => $thread,
        ]);
    }

    /**
     * @param ObjectManager $em
     * @param Message       $newRequest
     * @param Message       $hostingRequest
     * @param Message       $data
     * @param $clickedButton
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     *
     * @return Message
     */
    private function getFinalRequest(ObjectManager $em, Message $newRequest, Message $hostingRequest, Message $data, $clickedButton)
    {
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
        if ($oldState !== $newState) {
            $newRequest->getRequest()->setStatus($newState);
        }
        // check if new subject was set
        if ($data->getSubject()->getSubject() !== $hostingRequest->getSubject()->getSubject()) {
            $newSubject = new Subject();
            $newSubject->setSubject($data->getSubject()->getSubject());
            $em->persist($newSubject);
            $newRequest->setSubject($newSubject);
        } else {
            $newRequest->setSubject($hostingRequest->getSubject());
        }

        // check if request was altered
        $newArrival = ($data->getRequest()->getArrival() !== $hostingRequest->getRequest()->getArrival());
        $newDeparture = ($data->getRequest()->getDeparture() !== $hostingRequest->getRequest()->getDeparture());
        $newFlexible = ($data->getRequest()->getFlexible() !== $hostingRequest->getRequest()->getFlexible());
        $newNumberOfTravellers = ($data->getRequest()->getNumberOfTravellers() !== $hostingRequest->getRequest()->getNumberOfTravellers());
        if ($newArrival || $newDeparture || $newFlexible || $newNumberOfTravellers) {
            $newHostingRequest = new HostingRequest();
            $newHostingRequest->setArrival($data->getRequest()->getArrival());
            $newHostingRequest->setDeparture($data->getRequest()->getDeparture());
            $newHostingRequest->setFlexible($data->getRequest()->getFlexible());
            $newHostingRequest->setNumberOfTravellers($data->getRequest()->getNumberOfTravellers());
            $em->persist($newHostingRequest);
            $newRequest->setRequest($newHostingRequest);
        } else {
            $newRequest->setRequest($hostingRequest->getRequest());
        }
        $newRequest->setParent($hostingRequest);

        return $newRequest;
    }

    private function getSubMenuItems()
    {
        return [
            'both_inbox' => [
                'key' => 'MessagesRequestsReceived',
                'url' => $this->generateUrl('both', ['folder' => 'inbox']),
            ],
            'requests_inbox' => [
                'key' => 'RequestsReceived',
                'url' => $this->generateUrl('requests', ['folder' => 'inbox']),
            ],
            'requests_sent' => [
                'key' => 'RequestsSent',
                'url' => $this->generateUrl('requests', ['folder' => 'sent']),
            ],
            'messages_sent' => [
                'key' => 'MessagesSent',
                'url' => $this->generateUrl('messages', ['folder' => 'sent']),
            ],
            'messages_spam' => [
                'key' => 'MessagesSpam',
                'url' => $this->generateUrl('messages', ['folder' => 'spam']),
            ],
            'messages_deleted' => [
                'key' => 'MessagesDeleted',
                'url' => $this->generateUrl('messages', ['folder' => 'deleted']),
            ],
        ];
    }

    /**
     * @param Member $sender
     * @param Member $receiver
     * @param string $subject
     * @param string $body
     * @param string $template
     *
     * @return bool
     */
    private function sendMailNotification($sender, $receiver, $subject, $body, $template)
    {
        // Send mail notification
        $html2Text = new Html2Text($body);
        $message = new Swift_Message();
        if ('message' === $template) {
            $message
                ->setSubject($subject)
                ->setFrom(
                    [
                        'message@bewelcome.org' => 'BeWelcome - '.$sender->getUsername(),
                    ]
                );
        } else {
            $message
                ->setSubject('[Request] '.strip_tags($subject))
                ->setFrom(
                    [
                        'request@bewelcome.org' => 'BeWelcome - '.$sender->getUsername(),
                    ]
                );
        }
        $message
            ->setTo($receiver->getCryptedField('Email'))
            ->setBody(
                $this->renderView(
                    'emails/'.$template.'.html.twig',
                    [$template.'_text' => $body]
                ),
                'text/html'
            )
            ->addPart(
                $this->renderView(
                    'emails/'.$template.'.txt.twig',
                    [$template.'_text' => $html2Text->getText()]
                ),
                'text/plain'
            )
        ;
        $recipients = $this->get('mailer')->send($message);

        return (0 === $recipients) ? false : true;
    }

    private function checkRequestExpired(HostingRequest $request)
    {
        $requestModel = new RequestModel($this->getDoctrine());

        return $requestModel->checkRequestExpired($request);
    }
}
