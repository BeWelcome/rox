<?php

namespace App\Controller;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Language;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Preference;
use App\Entity\Subject;
use App\Form\CustomDataClass\MessageIndexRequest;
use App\Form\HostingRequestGuest;
use App\Form\HostingRequestHost;
use App\Form\MessageIndexFormType;
use App\Form\MessageToMemberType;
use App\Model\MessageModel;
use App\Model\RequestModel;
use Doctrine\Common\Persistence\ObjectManager;
use Html2Text\Html2Text;
use InvalidArgumentException;
use League\HTMLToMarkdown\HtmlConverter;
use Swift_Mailer;
use Swift_Message;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class RequestAndMessageController.
 *
 * Ignore complexity warning. \todo fix this.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class RequestAndMessageController extends AbstractController
{
    /** @var Swift_Mailer */
    private $mailer;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(Swift_Mailer $mailer, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

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
     * @throws AccessDeniedException
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
        $first = $thread[\count($thread) - 1];
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

        $view = (null === $message->getRequest()) ? 'message/view.html.twig' : 'request/view.html.twig';

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
        $sender = $this->getUser();
        if (!$receiver->isBrowseable()) {
            $this->addFlash('error', 'This member doesn\'t exist');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $messageModel = new MessageModel($this->getDoctrine());
        if ($messageModel->hasMessageLimitExceeded(
            $sender,
            $this->getParameter('new_members_messages_per_hour'),
            $this->getParameter('new_members_messages_per_day')
        )) {
            $this->addFlash('error', 'You have exceeded your message limit. Try again later');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $messageForm = $this->createForm(MessageToMemberType::class);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            // Write request to database after doing some checks
            $message = $messageForm->getData();
            $message->setSender($sender);
            $message->setReceiver($receiver);
            $message->setInfolder('Normal');
            $message->setWhenFirstRead(null);
            $message->setStatus(MessageStatusType::SENT);
            $message->setCreated(new \DateTime());

            $em = $this->getDoctrine()->getManager();
            $em->persist($message);

            $success = $this->sendMessageNotification(
                $sender,
                $receiver,
                $message
            );
            if ($success) {
                $this->addFlash('success', 'Message has been sent.');
                $message->setStatus('Sent');
                $em->persist($message);
            } else {
                $this->addFlash('notice', 'Message has been stored into the database. Mail notification couldn\'t be sent, though.');
            }
            $em->flush();

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render('message/message.html.twig', [
            'receiver' => $receiver,
            'form' => $messageForm->createView(),
        ]);
    }

    /**
     * @Route("/new/request/{username}", name="hosting_request")
     *
     * @param Request $request
     * @param Member  $host
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function newHostingRequestAction(Request $request, Member $host)
    {
        $member = $this->getUser();
        if ($member === $host) {
            $this->addFlash('notice', 'You can\'t send yourself a hosting request.');

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        if (!$host->isBrowseable()) {
            $this->addFlash('note', 'not browseable');
        }
        $messageModel = new MessageModel($this->getDoctrine());
        if ($messageModel->hasMessageLimitExceeded(
            $member,
            $this->getParameter('new_members_messages_per_hour'),
            $this->getParameter('new_members_messages_per_day')
        )) {
            $this->addFlash('error', 'You have exceeded your request limit. Try again later.');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        if (Member::ACC_NO === $host->getAccommodation()) {
            $this->addFlash('notice', 'request.not.hosting');

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
                $guest,
                $host,
                $hostingRequest
            );
            if ($success) {
                $this->addFlash('success', 'Request has been sent.');
            } else {
                $this->addFlash('notice', 'Request has been stored into the database. Mail notification couldn\'t be sent, though.');
            }

            return $this->redirectToRoute('members_profile', ['username' => $host->getUsername()]);
        }

        return $this->render('request/request.html.twig', [
            'receiver' => $host,
            'form' => $requestForm->createView(),
        ]);
    }

    /**
     * @Route("/messages/{folder}", name="messages",
     *     defaults={"folder": "inbox"})
     *
     * @param Request $request
     * @param string  $folder
     *
     * @throws InvalidArgumentException
     *
     * @return Response
     */
    public function messages(Request $request, $folder)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'datesent');
        $sortDir = $request->query->get('dir', 'desc');

        if (!\in_array($sortDir, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException();
        }

        $member = $this->getUser();
        $messageModel = new MessageModel($this->getDoctrine());
        $messages = $messageModel->getFilteredMessages($member, $folder, $sort, $sortDir, $page, $limit);

        return $this->handleFolderRequest($request, $folder, $messages, 'messages');
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
        $messageModel = new MessageModel($this->getDoctrine());
        $messages = $messageModel->getFilteredRequests($member, $folder, $sort, $sortDir, $page, $limit);

        return $this->handleFolderRequest($request, $folder, $messages, 'requests');
    }

    /**
     * @Route("/both/{folder}", name="both",
     *     defaults={"folder": "inbox"})
     *
     * @param Request $request
     * @param string  $folder
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function requestsAndMessages(Request $request, $folder)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'datesent');
        $sortDir = $request->query->get('dir', 'desc');

        if (!\in_array($sortDir, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException();
        }

        $member = $this->getUser();
        $messageModel = new MessageModel($this->getDoctrine());
        $messages = $messageModel->getFilteredRequestsAndMessages($member, $folder, $sort, $sortDir, $page, $limit);

        return $this->handleFolderRequest($request, $folder, $messages, 'both');
    }

    /**
     * @param Request $request
     * @param string  $folder
     * @param $messages
     * @param $type
     *
     * @return Response
     */
    private function handleFolderRequest(Request $request, $folder, $messages, $type)
    {
        $member = $this->getUser();
        $messageModel = new MessageModel($this->getDoctrine());
        $messageIds = [];
        foreach ($messages->getIterator() as $key => $val) {
            $messageIds[$key] = $val->getId();
        }
        $messageRequest = new MessageIndexRequest();
        $form = $this->createForm(MessageIndexFormType::class, $messageRequest, [
            'folder' => $folder,
            'ids' => $messageIds,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $messageIds = $data->getMessages();
            if (!empty($messages)) {
                if ($form->get('delete')->isClicked()) {
                    if ('deleted' === $folder) {
                        $messageModel->unmarkDeleted($member, $messageIds);
                        $this->addFlash('notice', 'Messages and/or requests undeleted (see respective folders).');
                    } else {
                        $messageModel->markDeleted($member, $messageIds);
                        $this->addFlash('notice', 'Messages and/or requests deleted (see deleted folder).');
                    }
                }
                if ($form->get('spam')->isClicked()) {
                    if ('spam' === $folder) {
                        $messageModel->unmarkAsSpam($messageIds);
                        $this->addFlash('notice', 'Messages marked as regular messages (and moved to inbox).');
                    } else {
                        $messageModel->markAsSpam($messageIds);
                        $this->addFlash('notice', 'Messages marked as spam messages (and moved to spam folder).');
                    }
                }

                return $this->redirect($request->getRequestUri());
            }
        }

        return $this->render('message/index.html.twig', [
            'form' => $form->createView(),
            'items' => $messages,
            'type' => 'UserMessages',
            'folder' => $folder,
            'filter' => $request->query->all(),
            'submenu' => [
                'active' => $type.'_'.$folder,
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
     * @throws \Exception
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
            $replyMessage->setWhenFirstRead(null);
            $replyMessage->setStatus(MessageStatusType::SENT);
            $replyMessage->setInfolder('Normal');
            $replyMessage->setCreated(new \DateTime());

            $replySubject = $replyMessage->getSubject()->getSubject();
            if (null !== $subject && $subject->getSubject() === $replySubject) {
                $replyMessage->setSubject($subject);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($replyMessage);
            $em->flush();

            $success = $this->sendMessageNotification(
                $sender,
                $receiver,
                $replyMessage
            );
            if ($success) {
                $this->addFlash('success', 'Reply has been sent.');
            } else {
                $this->addFlash('notice', 'Reply has been stored into the database. Mail notification couldn\'t be sent, though.');
            }

            return $this->redirectToRoute('message_show', ['id' => $replyMessage->getId()]);
        }

        return $this->render('message/reply.html.twig', [
            'form' => $messageForm->createView(),
            'current' => $message,
            'thread' => $thread,
        ]);
    }

    /**
     * @param Request       $request
     * @param array Message $thread
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * Ignore as too strict in this case (function is easily readable)
     */
    private function hostingRequestGuestReply(Request $request, array $thread)
    {
        $hostingRequest = $thread[0];
        if (null === $hostingRequest->getRequest()) {
            // This should never happen as it is handled in replyToMessageOrHostingRequest
            //so we throw an exception in this case
            throw new \InvalidArgumentException('wrong call to hosting reply guest');
        }

        $user = $this->getUser();
        /** @var Message $first */
        $first = $thread[\count($thread) - 1];
        $guest = $first->getSender();
        $host = $first->getReceiver();

        if ($user->getId() === $host->getId()) {
            // This should never happen so we throw an exception in this case
            throw new \InvalidArgumentException('wrong call to hosting reply guest');
        }

        if ($user->getId() !== $guest->getId()) {
            // This should never happen so we throw an exception in this case
            throw new \InvalidArgumentException('wrong call to hosting reply guest');
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
            $newRequest->setWhenFirstRead(null);
            $newRequest->setStatus(MessageStatusType::SENT);
            $em->persist($newRequest);
            $em->flush();

            $subject = $newRequest->getSubject()->getSubject();
            if ('Re:' !== substr($subject, 0, 3)) {
                $subject = 'Re: '.$subject;
            }

            if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
                $subject = 'Canceled: '.$subject;
            }

            $this->sendGuestReplyNotification(
                $guest,
                $host,
                $newRequest
            );
            $this->addFlash('success', 'Notification with updated information has been sent.');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($hostingRequest);

        return $this->render('request/reply_guest.html.twig', [
            'form' => $requestForm->createView(),
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
     * @throws InvalidArgumentException
     *
     * @return Response
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

        /** @var Form $requestForm */
        $requestForm = $this->createForm(HostingRequestHost::class, $newRequest);
        $requestForm->handleRequest($request);

        if ($requestForm->isSubmitted() && $requestForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $data = $requestForm->getData();
            $clickedButton = $requestForm->getClickedButton()->getName();
            $newRequest = $this->getFinalRequest($em, $newRequest, $hostingRequest, $data, $clickedButton);
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

            if (HostingRequest::REQUEST_CANCELLED === $newRequest->getRequest()->getStatus()) {
                $subject = 'Canceled: '.$subject;
            }

            $this->sendHostReplyNotification(
                $guest,
                $host,
                $newRequest
            );
            $this->addFlash('notice', 'Notification with updated information has been sent.');

            return $this->redirectToRoute('hosting_request_show', ['id' => $newRequest->getId()]);
        }

        $messageModel = new MessageModel($this->getDoctrine());
        $thread = $messageModel->getThreadForMessage($hostingRequest);

        return $this->render('request/reply_host.html.twig', [
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
        $diff = date_diff($data->getRequest()->getArrival(), $hostingRequest->getRequest()->getArrival());
        $newArrival = (0 !== $diff->y) && (0 !== $diff->m) && (0 !== $diff->d);
        $diff = date_diff($data->getRequest()->getDeparture(), $hostingRequest->getRequest()->getDeparture());
        $newDeparture = (0 !== $diff->y) && (0 !== $diff->m) && (0 !== $diff->d);
        $newFlexible = ($data->getRequest()->getFlexible() !== $hostingRequest->getRequest()->getFlexible());
        $newNumberOfTravellers = ($data->getRequest()->getNumberOfTravellers()
            !== $hostingRequest->getRequest()->getNumberOfTravellers());
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
            'requestsandmessages_inbox' => [
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
     * @param Member  $sender   Host/Guest
     * @param Member  $receiver Guest/Host
     * @param Message $message
     *
     * @return bool
     */
    private function sendMessageNotification(Member $sender, Member $receiver, Message $message)
    {
        // Send mail notification with the receiver's preferred locale
        $this->setTranslatorLocale($receiver);
        $subject = $message->getSubject()->getSubject();
        $messageBody = $message->getMessage();

        $body = $this->renderView('emails/message.html.twig', [
            'sender' => $sender,
            'receiver' => $receiver,
            'subject' => $subject,
            'body' => $messageBody,
        ]);

        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom([
                'message@bewelcome.org' => 'BeWelcome - '.$sender->getUsername(),
            ])
            ->setTo($receiver->getEmail())
            ->setBody(
                $body,
                'text/html'
            );
        $converter = new Html2Text($body, [
                'do_links' => 'table',
                'width' => 75]
        );
        $plainText = $converter->getText();
        $message
            ->addPart($plainText, 'text/plain')
        ;
        $recipients = $this->mailer->send($message);

        return (0 === $recipients) ? false : true;
    }

    private function sendNotification(Member $sender, Member $receiver, Message $request, $template)
    {
        // Send mail notification with the receiver's preferred locale
        $this->setTranslatorLocale($receiver);

        $subject = $request->getSubject()->getSubject();
        $message = $request->getMessage();
        $body = $this->renderView($template, [
            'sender' => $sender,
            'receiver' => $receiver,
            'subject' => $subject,
            'message' => $message,
            'request' => $request->getRequest(),
        ]);

        $message = (new Swift_Message())
            ->setSubject('[Request] '.strip_tags($subject))
            ->setFrom([
                'request@bewelcome.org' => 'BeWelcome - '.$receiver->getUsername(),
            ])
            ->setTo($receiver->getEmail())
            ->setBody(
                $body,
                'text/html'
            );
        $converter = new HtmlConverter([
            'strip_tags' => true,
            'remove_nodes' => 'head script style'
        ]);
        $plainText = $converter->convert($body);
        $message
            ->addPart($plainText, 'text/plain')
        ;
        $recipients = $this->mailer->send($message);

        return (0 === $recipients) ? false : true;
    }

    /**
     * @param Member  $guest
     * @param Member  $host
     * @param Message $request
     *
     * @return bool
     */
    private function sendInitialRequestNotification(Member $guest, Member $host, Message $request)
    {
        return $this->sendNotification($guest, $host, $request, 'emails/request.html.twig');
    }

    /**
     * @param Member  $guest
     * @param Member  $host
     * @param Message $request
     *
     * @return bool
     */
    private function sendHostReplyNotification(Member $guest, Member $host, Message $request)
    {
        return $this->sendNotification($guest, $host, $request, 'emails/reply_host.html.twig');
    }

    /**
     * @param Member  $guest
     * @param Member  $host
     * @param Message $request
     *
     * @return bool
     */
    private function sendGuestReplyNotification(Member $guest, Member $host, Message $request)
    {
        return $this->sendNotification($host, $guest, $request, 'emails/reply_guest.html.twig');
    }

    private function checkRequestExpired(HostingRequest $request)
    {
        $requestModel = new RequestModel($this->getDoctrine());

        return $requestModel->checkRequestExpired($request);
    }

    /**
     * Make sure to sent the email notification in the preferred language of the user
     *
     * @param Member $receiver
     */
    private function setTranslatorLocale(Member $receiver)
    {
        $preferenceRepository = $this->getDoctrine()->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::LOCALE]);

        $languageRepository = $this->getDoctrine()->getRepository(Language::class);
        /** @var Language $language */
        $language = $languageRepository->find($receiver->getMemberPreferenceValue($preference));

        $this->translator->setLocale($language->getShortcode());
    }
}
