<?php

namespace App\Controller;

use App\Doctrine\MessageStatusType;
use App\Entity\HostingRequest;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Form\MessageToMemberType;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class MessageController.
 *
 * Ignore complexity warning. \todo fix this.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class MessageController extends BaseMessageController
{
    /**
     * Deals with replies to messages and hosting requests.
     *
     * @Route("/message/{id}/reply", name="message_reply",
     *     requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param Message $message
     *
     * @return Response
     * @throws AccessDeniedException
     */
    public function replyToMessageAction(Request $request, Message $message)
    {
        $sender = $this->getUser();
        if (($message->getReceiver() !== $sender) && ($message->getSender() !== $sender)) {
            throw new AccessDeniedException;
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        $isHostingRequest = (null !== $message->getRequest()) ? true : false;
        if ($isHostingRequest) {
            return $this->redirectToRoute('hosting_request_reply', ['id' => $current->getId()]);
        }

        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('message_reply', ['id' => $current->getId()]);
        }

        return $this->messageReply($request, $sender, $thread);
    }

    /**
     * @Route("/message/{id}", name="message_show",
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
            $this->addTranslatedFlash('error', 'flash.member.invalid');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        if ($this->messageModel->hasMessageLimitExceeded(
            $sender,
            $this->getParameter('new_members_messages_per_hour'),
            $this->getParameter('new_members_messages_per_day')
        )) {
            $this->addTranslatedFlash('error', 'flash.message.limit');
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
            $em->flush();

            $success = $this->sendMessageNotification(
                $sender,
                $receiver,
                $message
            );
            if ($success) {
                $this->addTranslatedFlash('success', 'flash.message.sent');
                $message->setStatus('Sent');
                $em->persist($message);
            } else {
                $this->addTranslatedFlash('notice', 'flash.message.stored');
            }

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render('message/message.html.twig', [
            'receiver' => $receiver,
            'form' => $messageForm->createView(),
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
        $messages = $this->messageModel->getFilteredMessages($member, $folder, $sort, $sortDir, $page, $limit);

        return $this->handleFolderRequest($request, $folder, $messages, 'messages');
    }

    /**
     * @Route("/message/{id}/spam", name="message_mark_spam")
     *
     * @param Message $message
     *
     * @return Response
     */
    public function markAsSpamAction(Message $message)
    {
        $this->messageModel->markAsSpam([$message->getId()]);

        $this->addTranslatedFlash('notice', 'flash.marked.spam');

        return $this->redirectToRoute('message_show', ['id' => $message->getId()]);
    }

    /**
     * @Route("/message/{id}/nospam", name="message_mark_nospam")
     *
     * @param Message $message
     *
     * @return Response
     */
    public function unmarkAsSpamAction(Message $message)
    {
        $this->messageModel->unmarkAsSpam([$message->getId()]);

        $this->addTranslatedFlash('notice', 'flash.marked.nospam');

        return $this->redirectToRoute('message_show', ['id' => $message->getId()]);
    }

    /**
     * @Route("/all/messages/with/{username}", name="all_messages_with")
     *
     * @param Request $request
     * @param Member  $other
     *
     * @return Response
     * @throws InvalidArgumentException
     */
    public function allMessagesWithMember(Request $request, Member $other)
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $sort = $request->query->get('sort', 'datesent');
        $sortDir = $request->query->get('dir', 'desc');

        if (!\in_array($sortDir, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException();
        }

        $member = $this->getUser();
        $messages = $this->messageModel->getMessagesBetween($member, $other, $sort, $sortDir, $page, $limit);

        return $this->render('message/between.html.twig', [
            'items' => $messages,
            'otherMember' => $other,
            'submenu' => [
                'active' => 'between',
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
     * @throws Exception
     *
     * @return RedirectResponse|Response
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

            if ('Re:' !== substr($replySubject, 0, 3)) {
                $replySubject = 'Re: '.$replySubject;
            }

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
                $this->addTranslatedFlash('success', 'flash.reply.sent');
            } else {
                $this->addTranslatedFlash('notice', 'flash.reply.stored');
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
        $body = $this->renderView('emails/message.html.twig', [
            'sender' => $sender,
            'receiver' => $receiver,
            'message' => $message,
            'subject' => $subject,
        ]);

        // Reset to former locale as otherwise flash notification will be shown in receiver's locale
        $this->setTranslatorLocale($sender);

        return $this->sendEmail($sender, $receiver, $subject, $body);
    }
}
