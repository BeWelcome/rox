<?php

namespace AppBundle\Controller;

use AppBundle\Entity\HostingRequest;
use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use AppBundle\Entity\Subject;
use AppBundle\Form\MessageRequestType;
use AppBundle\Form\MessageToMemberType;
use AppBundle\Model\MessageModel;
use Html2Text\Html2Text;
use Rox\Core\Exception\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class MessageController extends Controller
{
    public function update(Request $request)
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

    /**
     * @Route("/message/{id}/reply", name="message_reply",
     *     requirements={"id": "\d+"})
     *
     * @param Message $message
     *
     * @return Response
     */
    public function reply(Request $request, Message $message)
    {
        $sender = $this->getUser();
        if (($message->getReceiver() !== $sender) && ($message->getSender() !== $sender)) {
            throw new AccessDeniedException();
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($message);

        $replyMessage = new Message();
        $replyMessage->setSubject($message->getSubject());

        $messageForm = $this->createForm(MessageToMemberType::class, $replyMessage);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid())
        {
            $receiver = ($message->getReceiver() === $sender) ? $message->getSender() : $message->getReceiver();
            $replyMessage = $messageForm->getData();
            $replyMessage->setParent($message);
            $replyMessage->setSender($sender);
            $replyMessage->setReceiver($receiver);
            $replyMessage->setInfolder('normal');
            $replyMessage->setCreated(new \DateTime());

            $subject = $message->getSubject();
            if ($subject->getSubject() != $replyMessage->getSubject()) {
                $subject = new Subject();
                $subject = $replyMessage->getSubject();
            }
            $replyMessage->setSubject($subject);
            $em = $this->getDoctrine()->getManager();
            $em->persist($replyMessage);
            $em->flush();

            // $replyMessage->refresh();
            return $this->redirectToRoute('message_show', [ 'id' => $replyMessage->getId()]);
        }


        return $content = $this->render(':message:reply.html.twig', [
            'form' => $messageForm->createView(),
            'current' => $message,
            'thread' => $thread
        ]);
    }

    /**
     * @Route("/message/{id}", name="message_show",
     *     requirements={"id": "\d+"})
     *
     * @param Message $message
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
            // Only mark as read when the receiver reads the message, not when
            // the message is presented to the Sender with url /messages/77/sent
            $message->setWhenfirstread(new \DateTime());
            $em = $this->getDoctrine()->getManager();
            $em->persist($message);
            $em->flush();
        }

        return $this->render(':message:view.html.twig', [
            'current' => $message,
            'thread' => $thread
        ]);
    }

    /**
     * @Route("/new/message/{username}", name="message_new")
     * @param Request $request
     * @param Member $receiver
     * @return Response
     */
    public function newMessageAction(Request $request, Member $receiver)
    {
        $messageForm = $this->createForm(MessageToMemberType::class);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            // Write request to database after doing some checks
            /** @var Message $hostingRequest */
            $hostingRequest = $messageForm->getData();
            $hostingRequest->setSender($this->getUser());
            $hostingRequest->setReceiver($receiver);
            $hostingRequest->setInfolder('normal');
            $hostingRequest->setCreated(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();
            $html2Text = new Html2Text($hostingRequest->getMessage());
            $hostingRequestText = $html2Text->getText();
            $message = \Swift_Message::newInstance()
                ->setSubject($hostingRequest->getSubject()->getSubject())
                ->setFrom('message@bewelcome.org')
                ->setTo($receiver->getCryptedField('Email'))
                ->setBody(
                    $this->renderView(
                    // app/Resources/views/Emails/registration.html.twig
                        'emails/request.html.twig',
                        ['request_text' => $hostingRequest->getMessage()]
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        'emails/request.txt.twig',
                        ['request_text' => $hostingRequestText]
                    ),
                    'text/plain'
                )
            ;
            $this->get('mailer')->send($message);
            $this->addFlash('success', 'Request has been sent.');
            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render(':message:message.html.twig', [
            'receiver' => $receiver,
            'form' => $messageForm->createView(),
        ]);

    }

    /**
     * @Route("/messages/{filter}", name="messages",
     *     requirements={ "filter": "requests|inbox|sent|spam|deleted" },
     *     defaults={"filter": "inbox"})
     *
     * @param Request $request
     * @param string  $filter
     *
     * @return Response
     */
    public function index(Request $request, $filter)
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
        $messages = $messageModel->getFilteredMessages($member, $filter, $sort, $sortDir, $page, $limit);

        return $this->render(':message:index.html.twig', [
            'messages' => $messages,
            'submenu' => [
                'active' => $filter,
                'route' => 'messages',
                'items' => [
                    'requests' => [
                        'key' => 'MessagesRequests',
                    ],
                    'inbox' => [
                        'key' => 'MessagesReceived',
                    ],
                    'sent' => [
                        'key' => 'MessagesSent',
                    ],
                    'spam' => [
                        'key' => 'MessagesSpam',
                    ],
                    'deleted' => [
                        'key' => 'MessagesDeleted',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @Route("/new/request/{username}", name="hosting_request")
     *
     * @param Member  $receiver
     * @param Request $request
     * @param Member  $receiver
     *
     * @return Response
     * @return Response
     */
    public function hostingRequest(Request $request, Member $receiver)
    {
        $requestForm = $this->createForm(MessageRequestType::class);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            // Write request to database after doing some checks
            /** @var Message $hostingRequest */
            $hostingRequest = $requestForm->getData();
            $hostingRequest->setSender($this->getUser());
            $hostingRequest->setReceiver($receiver);
            $hostingRequest->setInfolder('requests');
            $hostingRequest->setCreated(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($hostingRequest);
            $em->flush();
            $html2Text = new Html2Text($hostingRequest->getMessage());
            $hostingRequestText = $html2Text->getText();
            $message = \Swift_Message::newInstance()
                ->setSubject($hostingRequest->getSubject()->getSubject())
                ->setFrom('request@bewelcome.org')
                ->setTo($receiver->getCryptedField('Email'))
                ->setBody(
                    $this->renderView(
                    // app/Resources/views/Emails/registration.html.twig
                        'emails/request.html.twig',
                        ['request_text' => $hostingRequest->getMessage()]
                    ),
                    'text/html'
                )
                ->addPart(
                    $this->renderView(
                        'emails/request.txt.twig',
                        ['request_text' => $hostingRequestText]
                    ),
                    'text/plain'
                )
            ;
            $this->get('mailer')->send($message);
            $this->addFlash('success', 'Request has been sent.');
            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render(':message:request.html.twig', [
            'receiver' => $receiver,
            'form' => $requestForm->createView(),
        ]);
    }

    /**
     * @Route("/reply/request/{id}", name="hosting_request_reply")
     *
     * @param Request $request
     * @param Message $hostingRequest
     *
     * @return Response
     */
    public function hostingRequestReplyAction(Request $request, Message $hostingRequest)
    {
        if ($hostingRequest->getRequest() == null) {
            throw new InvalidArgumentException();
        }

        $requestForm = $this->createForm(MessageRequestType::class, $hostingRequest);
        $requestForm->handleRequest($request);

        return $this->render(':message:request.html.twig', [
            'receiver' => $hostingRequest->getReceiver(),
            'form' => $requestForm->createView(),
        ]);
    }
}
