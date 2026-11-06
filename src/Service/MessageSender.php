<?php

namespace App\Service;

use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Model\ConversationModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Sends a message from one member to another (no request): a new conversation, or a reply to one; then tells the
 * receiver by mail and in the browser, unless the message holds a blocked word (the checkers look at it first).
 */
class MessageSender
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ConversationModel $conversationModel,
        private readonly Mailer $mailer,
        private readonly BrowserNotificationService $browserNotificationService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param Message|null $parent the message answered, null for a new conversation
     */
    public function send(Member $sender, Member $receiver, ?Message $parent, string $subjectText, string $body): Message
    {
        $message = new Message();
        $message->setMessage($body);
        $message->setStatus('Sent');
        if (null === $parent) {
            $subject = new Subject();
            $subject->setSubject($subjectText);
            $request = null;
            $this->entityManager->persist($subject);
            $this->entityManager->flush();
            $message = $this->conversationModel->formatConversation($message);
        } else {
            $subject = $parent->getSubject();
            $request = $parent->getRequest();
        }

        $message->setSubject($subject);
        $message->setRequest($request);
        $message->setParent($parent);
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $this->entityManager->persist($message);
        $this->entityManager->flush();

        if (!str_contains($message->getSpamInfo(), SpamInfoType::SPAM_BLOCKED_WORD)) {
            $this->mailer->sendMessageNotificationEmail($sender, $receiver, 'message', [
                'message' => $message,
                'subject' => $subjectText,
                'body' => $body,
            ]);
            $this->browserNotificationService->queueAndSendAfterResponse(
                $receiver,
                BrowserNotificationPayload::message(
                    $sender,
                    $this->urlGenerator->generate('conversation_view', ['id' => $message->getId()])
                )
            );
        }

        return $message;
    }
}
