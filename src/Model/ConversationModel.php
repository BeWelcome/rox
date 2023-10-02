<?php

namespace App\Model;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\Mailer;
use App\Utilities\ConversationThread;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ConversationModel
{
    private Mailer $mailer;
    private EntityManagerInterface $entityManager;
    private ConversationThread $conversationThread;

    public function __construct(Mailer $mailer, EntityManagerInterface $entityManager)
    {
        $this->mailer = $mailer;
        $this->entityManager = $entityManager;
        $this->conversationThread = new ConversationThread($entityManager);
    }

    /**
     * Mark a conversation as purged (can not be unmarked).
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
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
     * @SuppressWarnings(PHPMD.StaticAccess)
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
     * @SuppressWarnings(PHPMD.StaticAccess)
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
     *
     * @param mixed $member
     * @param mixed $perHour
     * @param mixed $perDay
     */
    public function hasMessageLimitExceeded($member, $perHour, $perDay)
    {
        $sql = "
            SELECT
                (
                SELECT
                    COUNT(*)
                FROM
                    comments
                WHERE
                    comments.IdToMember = :id
                    AND
                    comments.Quality = 'Good'
                ) AS numberOfComments,
                (
                SELECT
                    COUNT(*)
                FROM
                    messages
                WHERE
                    messages.IdSender = :id
                    AND messages.IdParent IS NULL
                    AND messages.request_id IS NULL
                    AND
                    (
                        Status = 'ToSend'
                        OR
                        Status = 'Sent'
                    )
                    AND
                    DateSent > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ) AS numberOfMessagesLastHour,
                (
                SELECT
                    COUNT(*)
                FROM
                    messages
                WHERE
                    messages.IdSender = :id
                    AND messages.IdParent IS NULL
                    AND messages.request_id IS NULL
                    AND
                    (
                        Status = 'ToSend'
                        OR
                        Status = 'Sent'
                    )
                    AND
                    DateSent > DATE_SUB(NOW(), INTERVAL 1 DAY)
                ) AS numberOfMessagesLastDay
            ";

        return $this->hasLimitExceeded($member, $sql, $perHour, $perDay);
    }

    /**
     * Tests if a member has exceeded their limit for sending requests.
     *
     * @param mixed $member
     * @param mixed $perHour
     * @param mixed $perDay
     */
    public function hasRequestLimitExceeded($member, $perHour, $perDay): bool
    {
        $sql = "
            SELECT
                (
                SELECT
                    COUNT(*)
                FROM
                    comments
                WHERE
                    comments.IdToMember = :id
                    AND
                    comments.Quality = 'Good'
                ) AS numberOfComments,
                (
                SELECT
                    COUNT(*)
                FROM
                    messages
                WHERE
                    messages.IdSender = :id
                    AND NOT messages.request_id IS NULL
                    AND
                    (
                        Status = 'ToSend'
                        OR
                        Status = 'Sent'
                    )
                    AND
                    DateSent > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                ) AS numberOfMessagesLastHour,
                (
                SELECT
                    COUNT(*)
                FROM
                    messages
                WHERE
                    messages.IdSender = :id
                    AND NOT messages.request_id IS NULL
                    AND
                    (
                        Status = 'ToSend'
                        OR
                        Status = 'Sent'
                    )
                    AND
                    DateSent > DATE_SUB(NOW(), INTERVAL 1 DAY)
                ) AS numberOfMessagesLastDay
            ";

        return $this->hasLimitExceeded($member, $sql, $perHour, $perDay);
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
        /** @var MessageRepository */
        $messageRepository = $this->entityManager->getRepository(Message::class);
        /** @var Message[] $messages */
        $messages = $messageRepository->findBy(['subject' => $probableParent->getSubject()]);

        return $messages[\count($messages) - 1];
    }

    public function markConversationAsRead(Member $member, array $thread)
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

    private function hasLimitExceeded(Member $member, string $sql, int $perHour, int $perDay): bool
    {
        $id = $member->getId();

        $connection = $this->entityManager->getConnection();

        try {
            $statement = $connection->prepare($sql);
            $statement->bindValue(':id', $id);

            $result = $statement->executeQuery();
            $row = $result->fetchAssociative();
        } catch (Exception $e) {
            return false;
        }

        $comments = $row['numberOfComments'];
        $lastHour = $row['numberOfMessagesLastHour'];
        $lastDay = $row['numberOfMessagesLastDay'];

        if ($comments < 1 && ($lastHour >= $perHour || $lastDay >= $perDay)) {
            return true;
        }

        return false;
    }
}
