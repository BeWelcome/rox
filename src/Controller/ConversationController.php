<?php

namespace App\Controller;

use App\Doctrine\MemberStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use App\Form\HostingRequestGuest;
use App\Form\HostingRequestHost;
use App\Form\MessageToMemberType;
use App\Form\ReportSpamType;
use App\Model\ConversationModel;
use App\Model\HostingRequestModel;
use App\Service\InboxFolders;
use App\Utilities\ConversationThread;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * This controller handles all requests regarding single conversations (messages, hosting requests and invitations) like
 * viewing, replying, etc.
 */
class ConversationController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(
        protected ConversationModel $conversationModel,
        private EntityManagerInterface $entityManager,
        private InboxFolders $inboxFolders,
        private HostingRequestModel $requestModel,
    ) {
    }

    /**
     * \todo Check if this flag is needed (and used)).
     *
     * @SuppressWarnings("PHPMD.BooleanArgumentFlag")
     */
    #[Route(path: '/conversation/{id}', name: 'conversation_view', requirements: ['id' => '\d+'])]
    #[IsGranted('CONVERSATION_VIEW', subject: 'message')]
    public function viewConversation(Request $request, Message $message, bool $openReportModal = false): Response
    {
        return $this->viewThread($request, $message, false, $openReportModal);
    }

    #[Route(path: '/conversation/{id}/deleted', name: 'conversation_view_with_deleted', requirements: ['id' => '\d+'])]
    #[IsGranted('CONVERSATION_VIEW', subject: 'message')]
    public function viewConversationWithDeletedMessages(Request $request, Message $message): Response
    {
        return $this->viewThread($request, $message, true, false);
    }

    #[Route(path: '/conversation/{id}/reply', name: 'conversation_reply', requirements: ['id' => '\d+'])]
    #[IsGranted('CONVERSATION_VIEW', subject: 'message')]
    public function reply(Message $message): Response
    {
        // Always reply to the last item in the thread
        $conversationThread = new ConversationThread($this->entityManager);
        $thread = $conversationThread->getThread($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            // In case someone replies at the same time as the other side a redirect would kill the form.
            // Just set the last message and continue with the process.
            $message = $current;
        }

        // Check if member is part of this conversation
        /** @var Member $member */
        $member = $this->getUser();
        if ($member !== $message->getSender() && $member !== $message->getReceiver()) {
            return $this->redirectToRoute('conversations', ['conversationsType' => 'conversations']);
        }

        if (MemberStatusType::ACCOUNT_ACTIVATED === $member->getStatus()) {
            $receiver = ($message->getReceiver() === $member) ? $message->getSender() : $message->getReceiver();
            $this->addTranslatedFlash('notice', 'flash.conversation.not.active');

            return $this->redirectToRoute('members_profile', ['username' => $receiver->getUsername()]);
        }

        $controllerAndMethod = $this->getControllerAndMethod($message, 'reply');

        return $this->forward($controllerAndMethod, [
            'message' => $message,
        ]);
    }

    #[Route(path: '/conversation/{id}/delete', name: 'conversation_delete', requirements: ['id' => '\d+'])]
    #[IsGranted('CONVERSATION_VIEW', subject: 'message')]
    public function deleteConversation(Message $message): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->markConversationDeleted($member, $conversation);

        $this->addTranslatedFlash('notice', 'flash.marked.deleted');

        return $this->redirectToRoute('conversations_deleted');
    }

    #[Route(path: '/conversation/{id}/purge', name: 'conversation_purge', requirements: ['id' => '\d+'])]
    #[IsGranted('CONVERSATION_VIEW', subject: 'message')]
    public function purgeConversation(Message $message): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->markConversationPurged($member, $conversation);

        $this->addTranslatedFlash('notice', 'flash.marked.purged');

        return $this->redirectToRoute('conversations_deleted');
    }

    #[Route(path: '/conversation/{id}/recover', name: 'conversation_recover', requirements: ['id' => '\d+'])]
    #[IsGranted('CONVERSATION_VIEW', subject: 'message')]
    public function recoverConversation(Message $message): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->unmarkConversationDeleted($member, $conversation);

        $this->addTranslatedFlash('notice', 'flash.recovered');

        return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
    }

    #[Route(path: '/conversation/{id}/report', name: 'conversation_report_spam')]
    public function reportAsSpam(Request $request, Message $message): Response
    {
        return $this->viewThread($request, $message, false, true);
    }

    #[Route(path: '/conversation/{id}/decline', name: 'conversation_decline', requirements: ['id' => '\d+'])]
    #[IsGranted('CONVERSATION_VIEW', subject: 'message')]
    public function decline(Message $message): Response
    {
        if ($message->isMessage()) {
            return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
        }

        $controllerAndMethod = $this->getControllerAndMethod($message, 'decline');

        return $this->forward($controllerAndMethod, [
            'message' => $message,
        ]);
    }

    #[Route(path: '/conversation/{id}/nospam', name: 'conversation_mark_nospam')]
    public function unmarkAsSpam(Message $message): Response
    {
        /** @var Member $member */
        $member = $this->getUser();
        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->unmarkConversationAsSpam($member, $conversation);

        $this->addTranslatedFlash('notice', 'flash.marked.nospam');

        return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
    }

    private function markAsSpam(Message $message, ?string $comment): Response
    {
        /** @var Member $member */
        $member = $this->getUser();

        $conversationThread = new ConversationThread($this->entityManager);
        $conversation = $conversationThread->getThread($message);
        $this->conversationModel->markConversationAsSpam($member, $conversation, $comment);

        $this->addTranslatedFlash('notice', 'flash.marked.spam');

        return $this->redirectToRoute('conversation_view', ['id' => $message->getId()]);
    }

    private function getController(Message $message): string
    {
        $controller = '';
        if ($message->isMessage()) {
            $controller = MessageController::class;
        } elseif ($message->isHostingRequest()) {
            $controller = RequestController::class;
        } elseif ($message->isInvitation()) {
            $controller = InvitationController::class;
        }

        return $controller;
    }

    private function getControllerAndMethod(Message $message, string $method): string
    {
        $controller = $this->getController($message);

        return $controller . '::' . $method;
    }

    private function getViewTemplate(Message $message): string
    {
        $template = '';
        if ($message->isMessage()) {
            $template = 'message';
        } elseif ($message->isHostingRequest()) {
            $template = 'request';
        } elseif ($message->isInvitation()) {
            $template = 'invitation';
        }

        return $template . '/view.html.twig';
    }

    private function viewThread(
        Request $request,
        Message $message,
        bool $includeDeleted,
        bool $openReportModal,
    ): Response {
        $conversationThread = new ConversationThread($this->entityManager);
        $thread = $conversationThread->getThread($message);
        $current = $thread[0];

        if ($message->getId() !== $current->getId()) {
            return $this->viewThread($request, $current, $includeDeleted, $openReportModal);
        }
        // Now we're at the latest message in the thread. Check that not all items are deleted/purged depending on the
        // $showDeleted setting

        /** @var Member $member */
        $member = $this->getUser();

        $spam = $this->checkConversationIsSpam($thread, $member);
        $nothingVisible = $this->checkConversationIsAllDeleted($thread, $member, $includeDeleted);
        if ($nothingVisible) {
            return $this->redirectToRoute('conversations', ['conversationsType' => 'conversations']);
        }

        $this->conversationModel->markConversationAsRead($member, $thread);

        // A reply written under the conversation is posted to this same page.
        if ($current->isMessage() && $request->isMethod('POST') && $request->request->has('app_message')) {
            return $this->forward(self::class . '::reply', ['message' => $current]);
        }

        $reportForm = $this->createForm(ReportSpamType::class);
        $reportForm->handleRequest($request);
        if ($reportForm->isSubmitted() && $reportForm->isValid()) {
            $data = $reportForm->getData();

            return $this->markAsSpam($message, $data['comment']);
        }

        $template = $this->getViewTemplate($message);

        return $this->render($template, [
            'reply_form' => $this->createInlineReplyForm($current),
            'request_form' => ($includeDeleted || $spam) ? null : $this->createInlineRequestForm($current, $member),
            'show_deleted' => $includeDeleted,
            'show_report_modal' => $openReportModal,
            'form' => $reportForm->createView(),
            'is_spam' => $spam,
            'current' => $current,
            'thread' => $thread,
        ] + $this->inboxFolders->getContext($request, $member, $includeDeleted ? 'deleted' : ($spam ? 'spam' : 'conversations')));
    }

    /**
     * Plain messages are answered right under the thread (the form posts back to the
     * conversation page). Requests and invitations keep their own reply page.
     */
    private function createInlineReplyForm(Message $current): ?FormView
    {
        if (!$current->isMessage()) {
            return null;
        }

        $replyMessage = new Message();
        $replyMessage->setSubject($current->getSubject());

        return $this->createForm(MessageToMemberType::class, $replyMessage)->createView();
    }

    /**
     * Hosting requests are answered right under the thread too: the host picks accept,
     * maybe or decline, the guest can change the dates or cancel. The form posts to the
     * reply page, which saves it and comes back here. Expired requests and members who
     * can't write yet keep the link to the reply page, which explains why.
     */
    private function createInlineRequestForm(Message $current, Member $member): ?FormView
    {
        if (
            !$current->isHostingRequest()
            || ($member !== $current->getSender() && $member !== $current->getReceiver())
            || MemberStatusType::ACCOUNT_ACTIVATED === $member->getStatus()
            || $this->requestModel->hasExpired($current)
        ) {
            return null;
        }

        $reply = new Message();
        $reply->setSubject($current->getSubject());
        $reply->setMessage('');
        $reply->setInitiator($current->getInitiator());

        if ($member === $current->getInitiator()) {
            // The guest may change the dates: work on a copy so the shown request stays as it is.
            $reply->setRequest(clone $current->getRequest());

            return $this->createForm(HostingRequestGuest::class, $reply)->createView();
        }

        $reply->setRequest($current->getRequest());

        return $this->createForm(HostingRequestHost::class, $reply)->createView();
    }

    private function checkConversationIsAllDeleted(array $thread, Member $member, bool $includeDeleted): bool
    {
        $nothingVisible = true;
        foreach ($thread as $threadMessage) {
            if ($includeDeleted) {
                $nothingVisible = $nothingVisible && $threadMessage->isPurgedByMember($member);
            } else {
                $nothingVisible = $nothingVisible && (
                    $threadMessage->isPurgedByMember($member) || $threadMessage->isDeletedByMember($member)
                );
            }
        }

        return $nothingVisible;
    }

    private function checkConversationIsSpam(array $thread, Member $member): bool
    {
        $spam = false;
        foreach ($thread as $threadMessage) {
            if ($threadMessage->getReceiver() === $member) {
                $spam = $spam || str_contains((string) $threadMessage->getSpamInfo(), SpamInfoType::MEMBER_SAYS_SPAM);
            }
        }

        return $spam;
    }
}
