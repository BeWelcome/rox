<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Form\MessageToMemberType;
use App\Utilities\MailerTrait;
use App\Utilities\ManagerTrait;
use Exception;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
    use MailerTrait;
    use ManagerTrait;

    /**
     * Deals with replies to messages and hosting requests.
     *
     * @Route("/message/{id}/reply", name="message_reply",
     *     requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param Message $message
     *
     * @throws AccessDeniedException
     * @throws Exception
     *
     * @return Response
     */
    public function replyToMessage(Request $request, Message $message)
    {
        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        if ($this->isHostingRequest($message)) {
            return $this->redirectToHostingRequestReply($message);
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('message_reply', ['id' => $current->getId()]);
        }

        return $this->messageReply($request, $this->getUser(), $thread);
    }

    /**
     * Deals with deletion of messages and hosting requests.
     *
     * @Route("/message/{id}/delete/{redirect}", name="message_delete",
     *     requirements={"id": "\d+"})
     *
     * @param Request $request
     * @param Message $message
     * @ParamConverter("redirect", class="App\Entity\Message", options={"id": "redirect"})
     *
     * @throws AccessDeniedException
     * @throws Exception
     *
     * @return Response
     */
    public function deleteMessageOrRequest(Request $request, Message $message, Message $redirect)
    {
        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        /** @var Member $member */
        $member = $this->getUser();

        $this->messageModel->markDeleted($member, [$message->getId()]);
        $this->addTranslatedFlash('notice', 'flash.message.deleted');

        if ($message->getId() === $redirect->getId()) {
            return $this->redirectToRoute('messages', ['folder' => 'deleted']);
        }
        else
        {
            return $this->redirectToRoute('message_show', ['id' => $redirect->getId()]);
        }
    }

    /**
     * @Route("/message/{id}", name="message_show",
     *     requirements={"id": "\d+"})
     *
     * @param Message $message
     *
     * @throws Exception
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function show(Message $message)
    {
        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        if ($this->isHostingRequest($message)) {
            return $this->redirectToHostingRequest($message);
        }

        $thread = $this->messageModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('message_show', ['id' => $current->getId()]);
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
     * @throws Exception
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
            $subject = $messageForm->get('subject')->get('subject')->getData();
            $body = $messageForm->get('message')->getData();

            $this->messageModel->addMessage($sender, $receiver, null, $subject, $body);
            $this->addTranslatedFlash('success', 'flash.message.sent');

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
        $sort = $request->query->get('sort', 'dateSent');
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
     * @throws InvalidArgumentException
     *
     * @return Response
     */
    public function allMessagesWithMember(Request $request, Member $other)
    {
        list($page, $limit, $sort, $direction) = $this->getOptionsFromRequest($request);

        if (!\in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException();
        }

        /** @var Member $member */
        $member = $this->getUser();
        $messages = $this->messageModel->getMessagesBetween($member, $other, $sort, $direction, $page, $limit);

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
        $subject = $message->getSubject();
        if (null !== $subject) {
            $subjectText = $subject->getSubject();
            if ('Re:' !== substr($subjectText, 0, 3)) {
                $subjectText = 'Re: '.$subjectText;
            }
            $replyMessage->setSubject(new Subject());
            $replyMessage->getSubject()->setSubject($subjectText);
        }

        $messageForm = $this->createForm(MessageToMemberType::class, $replyMessage);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $replySubject = $messageForm->get('subject')->get('subject')->getData();
            if ('Re:' !== substr($replySubject, 0, 3)) {
                $replySubject = 'Re: '.$replySubject;
            }

            $messageText = $messageForm->get('message')->getData();
            $message = $this->messageModel->addMessage($sender, $receiver, $message, $replySubject, $messageText);
            $this->addTranslatedFlash('success', 'flash.reply.sent');

            return $this->redirectToRoute('message_show', ['id' => $message->getId()]);
        }

        return $this->render('message/reply.html.twig', [
            'form' => $messageForm->createView(),
            'current' => $message,
            'thread' => $thread,
        ]);
    }

    private function isHostingRequest(Message $message)
    {
        return (null !== $message->getRequest()) ? true : false;
    }

    private function redirectToHostingRequest(Message $message)
    {
        return $this->redirectToRoute('hosting_request_show', ['id' => $message->getId()]);
    }

    private function redirectToHostingRequestReply(Message $message)
    {
        return $this->redirectToRoute('hosting_request_reply', ['id' => $message->getId()]);
    }
}
