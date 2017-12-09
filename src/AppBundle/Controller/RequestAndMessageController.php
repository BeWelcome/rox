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
use InvalidArgumentException;
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
    /*    public function update(Request $request)
        {
            $modifyAction = $request->request->get('modify');
            $messageIds = $request->request->get('message_id');

            $member = $this->getUser();

            $message = new Message();

            $messages = $message->newQuery()->findMany($messageIds);

            foreach ($messages as $message) {
                if ($modifyAction === 'delete') {
                    $this->messageService->deleteMessage($message, $member);
                } elseif ($modifyAction === 'markasspam') {
                    $this->messageService->moveMessage($message, Message::FOLDER_SPAM);
                } elseif ($modifyAction === 'nospam') {
                    $this->messageService->moveMessage($message, Message::FOLDER_INBOX);
                //} else {
                    //throw new \InvalidArgumentException('Invalid message state.');
                }
            }

            return new RedirectResponse($request->getUri());
        }

        public function with(Request $request)
        {
            $page = $request->query->get('page', 1);
            $limit = $request->query->get('limit', 10);
            //$sort = $request->query->get('sort', 'date');
            //$dir = $request->query->get('dir', 'DESC');
            $otherUsername = $request->attributes->get('username');

            $otherMember = $this->memberRepository->getByUsername($otherUsername);

            $member = $this->getUser();

            $message = new Message();

            $q = $message->newQuery();

            // Eager load each sender for each message
            $q->with('sender');

            $q->where(function (Builder $builder) use ($member, $otherMember) {
                $builder->where(function (Builder $builder) use ($member, $otherMember) {
                    $builder->where('IdSender', $otherMember->id);
                    $builder->where('IdReceiver', $member->id);
                    $builder->where('Status', 'Sent');
                });

                $builder->orWhere(function (Builder $builder) use ($member, $otherMember) {
                    $builder->where('IdSender', $member->id);
                    $builder->where('IdReceiver', $otherMember->id);
                });
            });

            $q->where('DeleteRequest', 'NOT LIKE', '%receiverdeleted%');

            $q->orderByRaw('IF(messages.created > messages.DateSent, messages.created, messages.DateSent) DESC');

            $q->forPage($page, $limit);

            $count = $q->getQuery()->getCountForPagination();

            $messages = $q->get();

            $content = $this->render('@message/message/index.html.twig', [
                'messages' => $messages,
                'folder' => '',
                'filter' => $request->query->all(),
                'page' => $page,
                'pages' => ceil($count / $limit),
            ]);

            return new Response($content);
        }
    */

    /**
     * @Route("/message/{id}/reply", name="message_reply",
     *     requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param Message $message
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return Response
     */
    public function reply(Request $request, Message $message)
    {
        $sender = $this->getUser();
        if (($message->getReceiver() !== $sender) && ($message->getSender() !== $sender)) {
            throw new AccessDeniedException();
        }

        if (null !== $message->getRequest()) {
            return $this->redirectToRoute('hosting_request_reply', ['id' => $message->getId()]);
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($message);

        $replyMessage = new Message();
        $replyMessage->getSubject()->setSubject($message->getSubject()->getSubject());

        $messageForm = $this->createForm(MessageToMemberType::class, $replyMessage);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $receiver = ($message->getReceiver() === $sender) ? $sender : $message->getReceiver();
            $replyMessage = $messageForm->getData();
            $replyMessage->setParent($message);
            $replyMessage->setSender($sender);
            $replyMessage->setReceiver($receiver);
            $replyMessage->setInfolder('Normal');
            $replyMessage->setCreated(new \DateTime());

            $subject = $message->getSubject();
            $replySubject = $replyMessage->getSubject()->getSubject();
            if (null === $subject || $subject->getSubject() !== $replySubject) {
                $subject = $replyMessage->getSubject();
            }
            $replyMessage->setSubject($subject);
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

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($message);

        if ($message->isUnread() && $member === $message->getReceiver()) {
            // Only mark as read if it is a message and when the receiver reads the message,
            // not when the message is presented to the Sender with url /messages/{id}/sent
            $message->setWhenFirstRead(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
        }

        $view = (null === $message->getRequest()) ? ':message:view.html.twig' : ':request:view.html.twig';

        return $this->render($view, [
            'current' => $message,
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
            /** @var Message $hostingRequest */
            $sender = $this->getUser();
            $hostingRequest = $messageForm->getData();
            $hostingRequest->setSender($sender);
            $hostingRequest->setReceiver($receiver);
            $hostingRequest->setInfolder('Normal');
            $hostingRequest->setCreated(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();
            $html2Text = new Html2Text($hostingRequest->getMessage());
            $hostingRequestText = $html2Text->getText();
            $message = (new Swift_Message())
                ->setSubject($hostingRequest->getSubject()->getSubject())
                ->setFrom([
                    'message@bewelcome.org' => 'bewelcome - '.$sender->getUsername(),
                ])
                ->setTo($receiver->getCryptedField('Email'))
                ->setBody(
                    $this->renderView(
                        // app/Resources/views/Emails/registration.html.twig
                        'requests.html.twig',
                        ['request_text' => $hostingRequest->getMessage()]
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        'requests.txt.twig',
                        ['request_text' => $hostingRequestText]
                    ),
                    'text/plain'
                )
            ;
            $results = $this->get('mailer')->send($message);
            $this->get('logger')->addInfo('Message send: '.$results);
            $this->addFlash('success', 'Message has been sent.');

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

        if ($receiver->getAccommodation() == Member::ACC_NO) {
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
     * @Route("/request/{id}/reply/guest", name="hosting_request_reply_guest")
     *
     * @param Request $request
     * @param Message $hostingRequest
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * Ignore as too strict in this case (function is easily readable)
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return Response
     */
    public function hostingRequestGuestReplyAction(Request $request, Message $hostingRequest)
    {
        if (null === $hostingRequest->getRequest()) {
            return $this->redirectToRoute('message_show', ['id' => $hostingRequest->getId()]);
        }

        $user = $this->getUser();
        $sender = $hostingRequest->getSender();
        $receiver = $hostingRequest->getReceiver();

        if ($user->getId() === $receiver->getId()) {
            // This should have been /request/{id}/reply/host
            return $this->redirectToRoute('hosting_request_reply_host', ['id' => $hostingRequest->getId()]);
        }

        if ($user->getId() !== $sender->getId()) {
            return $this->redirectToRoute('requests', ['folder' => 'sent']);
        }

        if ($this->checkRequestExpired($hostingRequest->getRequest())) {
            $this->addFlash('information', 'This request can\'t be replied to anymore as the hosting period already started.');

            return $this->redirectToRoute('message_show', ['id' => $hostingRequest->getId()]);
        }

        // Make sure a new message has to be entered when changing the status of the request
        $newRequest = $this->getNewRequestFromOriginal($hostingRequest);

        $requestForm = $this->createForm(HostingRequestGuest::class, $newRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $requestForm->getData();
            $clickedButton = $requestForm->getClickedButton()->getName();
            $newRequest = $this->getFinalRequest($em, $newRequest, $hostingRequest, $data, $clickedButton);
            $em->persist($newRequest);
            $em->flush();

            $subject = $newRequest->getSubject()->getSubject();
            if ('Re:' !== substr($subject, 0, 3)) {
                $subject = 'Re: '.$subject;
            }

            if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
                $subject = 'Canceled! '.$subject;
            }

            $this->sendMailNotification(
                $sender,
                $receiver,
                $subject,
                $newRequest->getMessage(),
                'request'
            );
            $this->addFlash('success', 'Notification with updated information has been sent.');

            return $this->redirectToRoute('message_show', ['id' => $newRequest->getId()]);
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($hostingRequest);

        return $this->render(':request:reply_guest.html.twig', [
            'form' => $requestForm->createView(),
            'sender' => $sender,
            'receiver' => $receiver,
            'current' => $hostingRequest,
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/request/{id}/reply/host", name="hosting_request_reply_host")
     *
     * @param Request $request
     * @param Message $hostingRequest
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * Ignore as too strict in this case (function is easily readable)
     *
     * @return Response
     */
    public function hostingRequestHostReplyAction(Request $request, Message $hostingRequest)
    {
        if (null === $hostingRequest->getRequest()) {
            return $this->redirectToRoute('message_show', ['id' => $hostingRequest->getId()]);
        }

        $user = $this->getUser();
        $sender = $hostingRequest->getSender();
        $receiver = $hostingRequest->getReceiver();

        if ($user->getId() === $sender->getId()) {
            // This should have been /request/{id}/reply/guest
            return $this->redirectToRoute('hosting_request_reply_host', ['id' => $hostingRequest->getId()]);
        }

        if ($user->getId() !== $receiver->getId()) {
            return $this->redirectToRoute('requests', ['folder' => 'inbox']);
        }

        if ($this->checkRequestExpired($hostingRequest->getRequest())) {
            $this->addFlash('notice', 'This request can\'t be replied to anymore as the hosting period already started.');

            return $this->redirectToRoute('message_show', ['id' => $hostingRequest->getId()]);
        }

        // Make sure a new message has to be entered when changing the status of the request
        $newRequest = $this->getNewRequestFromOriginal($hostingRequest);

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
                $subject = 'Canceled! '.$subject;
            }

            $this->sendMailNotification(
                $receiver,
                $sender,
                $subject,
                $newRequest->getMessage(),
                'request'
            );
            $this->addFlash('notice', 'Notification with updated information has been sent.');

            return $this->redirectToRoute('message_show', ['id' => $newRequest->getId()]);
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($hostingRequest);

        return $this->render(':request:reply_host.html.twig', [
            'form' => $requestForm->createView(),
            'sender' => $sender,
            'receiver' => $receiver,
            'current' => $hostingRequest,
            'thread' => $thread,
        ]);
    }

    /**
     * @Route("/messages/{folder}", name="messages",
     *     requirements={ "folder": "requests|inbox|sent|spam|deleted" },
     *     defaults={"folder": "inbox"})
     *
     * @param Request $request
     * @param string  $folder
     *
     * @return Response
     */
    public function messages(Request $request, $folder)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'datesent');
        $sortDir = $request->query->get('dir', 'desc');

        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException();
        }

        $member = $this->getUser();

        $messageModel = new MessageModel($this->getDoctrine());
        $messages = $messageModel->getFilteredMessages($member, $folder, $sort, $sortDir, $page, $limit);

        return $this->render(':message:index.html.twig', [
            'items' => $messages,
            'type' => 'UserMessages',
            'folder' => $folder,
            'filter' => $request->query->all(),
            'submenu' => [
                'active' => 'messages_'.$folder,
                'items' => $this->getSubMenuItems(),
            ],
        ]);
    }

    /**
     * @Route("/requests/{folder}", name="requests",
     *     requirements={ "folder": "inbox|sent" },
     *     defaults={"folder": "inbox"})
     *
     * @param Request $request
     * @param string  $folder
     *
     * @return Response
     */
    public function requests(Request $request, $folder)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'datesent');
        $sortDir = $request->query->get('dir', 'desc');

        if (!in_array($sortDir, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException();
        }

        $member = $this->getUser();

        $requestModel = new RequestModel($this->getDoctrine());
        $requests = $requestModel->getFilteredRequests($member, $folder, $sort, $sortDir, $page, $limit);

        return $this->render(':message:index.html.twig', [
            'items' => $requests,
            'type' => 'UserRequests',
            'folder' => $folder,
            'filter' => $request->query->all(),
            'submenu' => [
                'active' => 'requests_'.$folder,
                'route' => 'messages',
                'items' => $this->getSubMenuItems(),
            ],
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
            $hostingRequest->getRequest()->setStatus($newState);
        }
        // check if new subject was set
        if ($data->getSubject()->getSubject() !== $hostingRequest->getSubject()->getSubject()) {
            $newSubject = new Subject();
            $newSubject->setSubject($data->getSubject()->getSubject());
            $hostingRequest->setSubject($newSubject);
            $em->persist($newSubject);
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
        } else {
            $newRequest->setRequest($hostingRequest->getRequest());
        }
        $newRequest->setParent($hostingRequest);

        return $newRequest;
    }

    private function getSubMenuItems()
    {
        return [
            'requests_inbox' => [
                'key' => 'MessagesRequestsReceived',
                'url' => $this->generateUrl('requests', ['folder' => 'inbox']),
            ],
            'requests_sent' => [
                'key' => 'MessagesRequestsSent',
                'url' => $this->generateUrl('requests', ['folder' => 'sent']),
            ],
            'messages_inbox' => [
                'key' => 'MessagesReceived',
                'url' => $this->generateUrl('messages', ['folder' => 'inbox']),
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
        $message = (new Swift_Message())
            ->setSubject('[Request] '.strip_tags($subject))
            ->setFrom([
                'request@bewelcome.org' => 'BeWelcome - '.$sender->getUsername(),
            ])
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

    private function getNewRequestFromOriginal(Message $hostingRequest)
    {
        $newRequest = new Message();
        $newRequest->setSubject(new Subject());
        $newRequest->getSubject()->setSubject($hostingRequest->getSubject()->getSubject());
        $newRequest->setRequest(new HostingRequest());
        $newRequest->getRequest()->setArrival($hostingRequest->getRequest()->getArrival());
        $newRequest->getRequest()->setDeparture($hostingRequest->getRequest()->getDeparture());
        $newRequest->getRequest()->setFlexible($hostingRequest->getRequest()->getFlexible());
        $newRequest->getRequest()->setNumberOfTravellers($hostingRequest->getRequest()->getNumberOfTravellers());
        $newRequest->setReceiver($hostingRequest->getReceiver());
        $newRequest->setSender($hostingRequest->getSender());

        return $newRequest;
    }

    private function checkRequestExpired(HostingRequest $request)
    {
        $requestModel = new RequestModel($this->getDoctrine());
        return $requestModel->checkRequestExpired($request);
    }
}
