<?php

namespace App\Model;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Comment;
use App\Entity\Member;
use App\Entity\Message;
use App\Repository\CommentRepository;
use App\Repository\MessageRepository;
use App\Service\Mailer;
use App\Utilities\ConversationThread;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Normalizer;
use Symfony\Contracts\Translation\TranslatorInterface;

class ConversationModel
{
    private readonly ConversationThread $conversationThread;

    public function __construct(private readonly Mailer $mailer, private readonly EntityManagerInterface $entityManager, private readonly TranslatorInterface $translator)
    {
        $this->conversationThread = new ConversationThread($this->entityManager);
    }

    /**
     * Mark a conversation as purged (can not be unmarked).
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function markConversationPurged(Member $member, array $conversation): void
    {
        /** @var Message $message */
        foreach ($conversation as $message) {
            $deleteRequest = $message->getDeleteRequest();
            if ($message->getReceiver() === $member) {
                $deleteRequest = DeleteRequestType::addReceiverPurged($deleteRequest);
            } elseif ($message->getSender() === $member) {
                $deleteRequest = DeleteRequestType::addSenderPurged($deleteRequest);
            }
            $message->setDeleteRequest($deleteRequest);
            $message->setFolder('Normal');
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    /**
     * Mark a conversation as deleted for this member.
     *
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function markConversationDeleted(Member $member, array $conversation): void
    {
        /** @var Message $message */
        foreach ($conversation as $message) {
            $deleteRequest = $message->getDeleteRequest();
            if ($message->getReceiver() === $member) {
                $deleteRequest = DeleteRequestType::addReceiverDeleted($deleteRequest);
            } elseif ($message->getSender() === $member) {
                $deleteRequest = DeleteRequestType::addSenderDeleted($deleteRequest);
            }
            $message->setDeleteRequest($deleteRequest);
            $message->setFolder('Normal');
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function unmarkConversationDeleted(Member $member, array $conversation): void
    {
        foreach ($conversation as $message) {
            $deleteRequest = $message->getDeleteRequest();
            if ($message->getReceiver() === $member) {
                $deleteRequest = DeleteRequestType::removeReceiverDeleted($deleteRequest);
            } elseif ($message->getSender() === $member) {
                $deleteRequest = DeleteRequestType::removeSenderDeleted($deleteRequest);
            }
            $message->setDeleteRequest($deleteRequest);
            $message->setFolder('Normal');
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    public function markConversationAsSpam(Member $member, array $conversation, ?string $comment = null): void
    {
        /** @var Message $message */
        foreach ($conversation as $message) {
            $message
                ->setCheckerComment($comment)
                ->setFolder(InFolderType::SPAM);
            if ($member === $message->getReceiver()) {
                $message
                    ->setStatus(MessageStatusType::CHECK)
                    ->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM);
            }
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    public function unmarkConversationAsSpam(Member $member, array $conversation): void
    {
        /** @var Message $message */
        foreach ($conversation as $message) {
            $message
                ->setFolder(InFolderType::NORMAL);
            if ($member === $message->getReceiver()) {
                $message
                    ->setStatus(MessageStatusType::CHECKED)
                    ->setSpamInfo(SpamInfoType::NO_SPAM)
                ;
            }
            $this->entityManager->persist($message);
        }
        $this->entityManager->flush();
    }

    /**
     * Tests if a member has exceeded their limit for sending messages.
     */
    public function hasMessageLimitExceeded(Member $member, int $perHour, int $perDay): bool
    {
        return $this->hasLimitExceeded($member, false, $perHour, $perDay);
    }

    /**
     * Tests if a member has exceeded their limit for sending requests.
     */
    public function hasRequestLimitExceeded(Member $member, int $perHour, int $perDay): bool
    {
        return $this->hasLimitExceeded($member, true, $perHour, $perDay);
    }

    public function getThreadInformationForMessage(Message $message): array
    {
        $thread = $this->conversationThread->getThread($message);
        $first = $thread[\count($thread) - 1];
        $last = $thread[0];
        $guest = $first->getSender();
        $host = $first->getReceiver();

        return [$thread, $first, $last, $guest, $host];
    }

    public function getLastMessageInConversation(Message $probableParent): Message
    {
        // Check if there is already a newer message than the one used for the request
        // as there might be a clash of replies
        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);
        /** @var Message[] $messages */
        $messages = $messageRepository->findBy(['subject' => $probableParent->getSubject()]);

        return $messages[\count($messages) - 1];
    }

    public function markConversationAsRead(Member $member, array $thread): void
    {
        // Walk through the thread and mark all messages as read (for current member)
        $em = $this->entityManager;
        foreach ($thread as $item) {
            if ($member === $item->getReceiver() && null === $item->getFirstRead()) {
                // Only mark as read if it is a message and when the receiver reads the message,
                // not when the message is presented to the Sender with url /messages/{id}/sent
                $item->setFirstRead(new DateTime());
                $em->persist($item);
            }
        }
        $em->flush();
    }

    public function formatConversation(Message $message): Message
    {
        $messageText = preg_replace('/[\x{200B}-\x{200D}\x{FEFF}]/u', '', $message->getMessage());
        $normalized = Normalizer::normalize($messageText, Normalizer::FORM_KC);
        if (false === $normalized) {
            $normalized = $messageText;
        }
        $found = preg_match("/@|\.at\.|-at-|\(at\)|verif|\.shop|system|support/i", $normalized);

        if (0 !== $found) {
            $message->setSpamInfo(SpamInfoType::SPAM_BLOCKED_WORD);
            $message->setFolder(InFolderType::SPAM);
            $message->setStatus(MessageStatusType::CHECK);
            $message->setMessage($messageText . '<p>Potential spam. Please report if necessary.</p>');
        }

        return $message;
    }

    /**
     * New members, those without a positive comment, may only send a few messages or requests per hour and day.
     */
    private function hasLimitExceeded(Member $member, bool $requests, int $perHour, int $perDay): bool
    {
        /** @var CommentRepository $commentRepository */
        $commentRepository = $this->entityManager->getRepository(Comment::class);
        if ($commentRepository->countPositiveCommentsAbout($member) > 0) {
            return false;
        }

        /** @var MessageRepository $messageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);

        return $messageRepository->countSentSince($member, new DateTime('-1 hour'), $requests) >= $perHour
            || $messageRepository->countSentSince($member, new DateTime('-1 day'), $requests) >= $perDay;
    }
}
