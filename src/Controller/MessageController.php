<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Form\MessageToMemberType;
use App\Utilities\ManagerTrait;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;
use InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
    use ManagerTrait;

    /**
     * Deals with replies to messages and hosting requests.
     *
     * @Route("/message/{id}/reply", name="message_reply",
     *     requirements={"id": "\d+"})
     *
     * @throws AccessDeniedException
     * @throws Exception
     */
    public function replyToMessage(Request $request, Message $message): Response
    {
        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        if (!$this->isMessage($message)) {
            return $this->redirectToHostingRequestReply($message);
        }

        $thread = $this->requestModel->getThreadForMessage($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            return $this->redirectToRoute('message_reply', ['id' => $current->getId()]);
        }

        /** @var Member $member */
        $member = $this->getUser();

        return $this->messageReply($request, $member, $thread);
    }

    /**
     * Deals with deletion of messages and hosting requests.
     *
     * @Route("/message/{id}/delete/{redirect}", name="message_delete",
     *     requirements={"id": "\d+"})
     *
     * @ParamConverter("redirect", class="App\Entity\Message", options={"id": "redirect"})

     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteMessageOrRequest(Message $message, Message $redirect): Response
    {
        if (!$this->isMessageOfMember($message)) {
            throw $this->createAccessDeniedException('Not your message/hosting request');
        }

        /** @var Member $member */
        $member = $this->getUser();

        $this->requestModel->markDeleted($member, [$message->getId()]);
        $this->addTranslatedFlash('notice', 'flash.message.deleted');

        $redirectRoute = 'message_show';
        if ($message->isDeletedByMember($member)) {
            $redirectRoute = 'message_show_with_deleted';
        }

        return $this->redirectToRoute($redirectRoute, ['id' => $redirect->getId()]);
    }

    /**
     * @Route("/message/{id}", name="message_show",
     *     requirements={"id": "\d+"})
     *
     * @throws Exception
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function show(Message $message)
    {
        if ($this->isHostingRequest($message)) {
            return $this->redirectToHostingRequest($message);
        }

        return $this->showThread($message, 'message/view.html.twig', 'message_show');
    }

    /**
     * @Route("/message/{id}/deleted", name="message_show_with_deleted",
     *     requirements={"id": "\d+"})
     *
     * @throws Exception
     * @throws AccessDeniedException
     *
     * @return Response
     */
    public function showDeleted(Message $message)
    {
        return $this->showThreadWithDeleted($message, 'message/view.html.twig', 'message_show_with_deleted');
    }

    /**
     * @Route("/new/message/{username}", name="message_new")
     *
     * @throws Exception
     */
    public function newMessageAction(Request $request, Member $receiver): Response
    {
        /** @var Member $sender */
        $sender = $this->getUser();
        if (!$receiver->isBrowseable()) {
            $this->addTranslatedFlash('error', 'flash.member.invalid');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        if (
            $this->requestModel->hasMessageLimitExceeded(
                $sender,
                $this->getParameter('new_members_messages_per_hour'),
                $this->getParameter('new_members_messages_per_day')
            )
        ) {
            $this->addTranslatedFlash('error', 'flash.message.limit');
            $referrer = $request->headers->get('referer');

            return $this->redirect($referrer);
        }

        $messageForm = $this->createForm(MessageToMemberType::class);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $subject = $messageForm->get('subject')->get('subject')->getData();
            $body = $messageForm->get('message')->getData();

            $this->requestModel->addMessage($sender, $receiver, null, $subject, $body);
            $this->addTranslatedFlash('success', 'flash.message.sent');

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        return $this->render('message/message.html.twig', [
            'receiver' => $receiver,
            'form' => $messageForm->createView(),
        ]);
    }

    /**
     * @Route("/messages_b/{folder}", name="messages_b",
     *     defaults={"folder": "inbox"})
     *
     * @throws InvalidArgumentException
     */
    public function messages(Request $request, string $folder): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        list($page, $limit, $sort, $direction) = $this->getOptionsFromRequest($request);

        $messages = $this->requestModel->getFilteredMessages(
            $member,
            $folder,
            $sort,
            $direction,
            $page,
            $limit
        );

        return $this->handleFolderRequest($request, $folder, 'messages', $messages);
    }

    /**
     * @Route("/message/{id}/spam", name="message_mark_spam")
     */
    public function markAsSpamAction(Message $message): Response
    {
        $this->requestModel->markAsSpam([$message->getId()]);

        $this->addTranslatedFlash('notice', 'flash.marked.spam');

        return $this->redirectToRoute('message_show', ['id' => $message->getId()]);
    }

    /**
     * @Route("/message/{id}/nospam", name="message_mark_nospam")
     */
    public function unmarkAsSpamAction(Message $message): Response
    {
        $this->requestModel->unmarkAsSpam([$message->getId()]);

        $this->addTranslatedFlash('notice', 'flash.marked.nospam');

        return $this->redirectToRoute('message_show', ['id' => $message->getId()]);
    }

    /**
     * @Route("/all/messages/with/{username}", name="all_messages_with")
     *
     * @throws InvalidArgumentException
     */
    public function allMessagesWithMember(Request $request, Member $other): Response
    {
        list($page, $limit, $sort, $direction) = $this->getOptionsFromRequest($request);

        if (!\in_array($direction, ['asc', 'desc'], true)) {
            throw new InvalidArgumentException();
        }

        /** @var Member $member */
        $member = $this->getUser();
        $messages = $this->requestModel->getMessagesBetween($member, $other, $sort, $direction, $page, $limit);

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
                $subjectText = 'Re: ' . $subjectText;
            }
            $replyMessage->setSubject(new Subject());
            $replyMessage->getSubject()->setSubject($subjectText);
        }

        $messageForm = $this->createForm(MessageToMemberType::class, $replyMessage);
        $messageForm->handleRequest($request);

        if ($messageForm->isSubmitted() && $messageForm->isValid()) {
            $replySubject = $messageForm->get('subject')->get('subject')->getData();
            if ('Re:' !== substr($replySubject, 0, 3)) {
                $replySubject = 'Re: ' . $replySubject;
            }

            $messageText = $messageForm->get('message')->getData();
            $message = $this->requestModel->addMessage($sender, $receiver, $message, $replySubject, $messageText);
            $this->addTranslatedFlash('success', 'flash.reply.sent');

            return $this->redirectToRoute('message_show', ['id' => $message->getId()]);
        }

        return $this->render('message/reply.html.twig', [
            'form' => $messageForm->createView(),
            'current' => $message,
            'thread' => $thread,
        ]);
    }

    private function redirectToHostingRequest(Message $message): RedirectResponse
    {
        return $this->redirectToRoute('hosting_request_show', ['id' => $message->getId()]);
    }

    private function redirectToHostingRequestReply(Message $message): RedirectResponse
    {
        return $this->redirectToRoute('hosting_request_reply', ['id' => $message->getId()]);
    }
}
