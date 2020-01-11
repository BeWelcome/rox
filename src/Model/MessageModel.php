<?php

namespace App\Model;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageStatusType;
use App\Doctrine\SpamInfoType;
use App\Entity\Member;
use App\Entity\Message;
use App\Entity\Subject;
use App\Repository\MessageRepository;
use App\Utilities\MailerTrait;
use App\Utilities\ManagerTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Pagerfanta\Pagerfanta;
use PDO;

/**
 * Class MessageModel.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * Hide logic in DeleteRequestType
 */
class MessageModel
{
    use MailerTrait;
    use ManagerTrait;
    use TranslatorTrait;

    /**
     * Mark a message as purged (can not be unmarked).
     *
     * @param Member $member
     * @param array  $messageIds
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function markPurged(Member $member, array $messageIds)
    {
        $em = $this->getManager();

        /** @var MessageRepository $repository */
        $repository = $em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            if ($message->getReceiver()->getId() === $member->getId()) {
                $deleteRequest = DeleteRequestType::addReceiverPurged($message->getDeleteRequest());
            } else {
                $deleteRequest = DeleteRequestType::addSenderPurged($message->getDeleteRequest());
            }
            $message->setDeleteRequest($deleteRequest);
            $em->persist($message);
        }
        $em->flush();
    }

    /**
     * @param Member $member
     * @param array  $messageIds
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function markDeleted(Member $member, array $messageIds)
    {
        $em = $this->getManager();

        /** @var MessageRepository $repository */
        $repository = $em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            if ($message->getReceiver()->getId() === $member->getId()) {
                $deleteRequest = DeleteRequestType::addReceiverDeleted($message->getDeleteRequest());
            } else {
                $deleteRequest = DeleteRequestType::addSenderDeleted($message->getDeleteRequest());
            }
            $message->setDeleteRequest($deleteRequest);
            $em->persist($message);
        }
        $em->flush();
    }

    /**
     * @param Member $member
     * @param array  $messageIds
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function unmarkDeleted(Member $member, array $messageIds)
    {
        $em = $this->getManager();
        /** @var MessageRepository $repository */
        $repository = $em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            if ($message->getReceiver()->getId() === $member->getId()) {
                $deleteRequest = DeleteRequestType::removeReceiverDeleted($message->getDeleteRequest());
            } else {
                $deleteRequest = DeleteRequestType::removeSenderDeleted($message->getDeleteRequest());
            }
            $message->setDeleteRequest($deleteRequest);
            $em->persist($message);
        }
        $em->flush();
    }

    /**
     * @param array $messageIds
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function markAsSpamByChecker(array $messageIds)
    {
        $em = $this->getManager();
        /** @var MessageRepository $repository */
        $repository = $em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            $message
                ->setStatus(MessageStatusType::CHECKED)
                ->addToSpamInfo(SpamInfoType::CHECKER_SAYS_SPAM);
            $em->persist($message);
        }
        $em->flush();
    }

    /**
     * @param array $messageIds
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function unmarkAsSpamByChecker(array $messageIds)
    {
        $em = $this->getManager();
        /** @var MessageRepository $repository */
        $repository = $em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            // \todo If message wasn't sent yet, send it now
            if (MessageStatusType::FROZEN === $message->getStatus()) {
                $message->setStatus(MessageStatusType::SEND);
            } else {
                $message->setStatus(MessageStatusType::CHECKED);
            }
            $em->persist($message);
        }
        $em->flush();
    }

    /**
     * @param array $messageIds
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function markAsSpam(array $messageIds)
    {
        $em = $this->getManager();
        /** @var MessageRepository $repository */
        $repository = $em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            $message
                ->setFolder(InFolderType::SPAM)
                ->setStatus(MessageStatusType::CHECK)
                ->addToSpamInfo(SpamInfoType::MEMBER_SAYS_SPAM)
            ;
            $em->persist($message);
        }
        $em->flush();
    }

    /**
     * @param array $messageIds
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function unmarkAsSpam(array $messageIds)
    {
        $em = $this->getManager();
        /** @var MessageRepository $repository */
        $repository = $em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            $message
                ->setFolder(InFolderType::NORMAL)
                ->setStatus(MessageStatusType::CHECKED)
                ->removeFromSpaminfo(SpamInfoType::MEMBER_SAYS_SPAM)
            ;
            $em->persist($message);
        }
        $em->flush();
    }

    /**
     * @param int $page
     * @param int $limit
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getReportedMessages($page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->getManager()->getRepository(Message::class);

        return $repository->findReportedMessages($page, $limit);
    }

    /**
     * @param $member
     * @param $folder
     * @param $sort
     * @param $sortDir
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getFilteredMessages($member, $folder, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->getManager()->getRepository(Message::class);

        return $repository->findLatestMessages($member, $folder, $sort, $sortDir, $page, $limit);
    }

    /**
     * @param $member
     * @param $folder
     * @param $sort
     * @param $sortDir
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getFilteredRequests($member, $folder, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->getManager()->getRepository(Message::class);

        return $repository->findLatestRequests($member, $folder, $sort, $sortDir, $page, $limit);
    }

    /**
     * @param $member
     * @param $folder
     * @param $sort
     * @param $sortDir
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getFilteredRequestsAndMessages($member, $folder, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->getManager()->getRepository(Message::class);

        return $repository->findLatestRequestsAndMessages($member, $folder, $sort, $sortDir, $page, $limit);
    }

    /**
     * @param Member $member
     * @param Member $other
     * @param $sort
     * @param $sortDir
     * @param int $page
     * @param int $limit
     *
     * @return Pagerfanta
     */
    public function getMessagesBetween(Member $member, Member $other, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->getManager()->getRepository(Message::class);

        return $repository->findAllMessagesBetween($member, $other, $sort, $sortDir, $page, $limit);
    }

    /**
     * Returns the thread that contains the given message.
     *
     * @param Message $message
     *
     * @return Message[]
     */
    public function getThreadForMessage(Message $message)
    {
        $result = [];
        try {
            $connection = $this->getManager()->getConnection();
            $stmt = $connection->prepare('
                SELECT
                    id
                FROM
                (SELECT
                        id, parent, IF(ancestry, @cl:=@cl + 1, level + @cl) AS level
                    FROM
                    (SELECT
                        TRUE AS ancestry, _id AS id, parent, level
                    FROM
                    (SELECT
                        @r AS _id,
                            (SELECT
                                    @r:=Idparent
                                FROM
                                    messages
                                WHERE
                                    id = _id) AS parent,
                            @l:=@l + 1 AS level
                    FROM
                    (SELECT @r:=:message_id, @l:=0, @cl:=0) vars, messages h
                    WHERE
                        @r <> 0
                    ORDER BY level DESC) qi UNION ALL SELECT
                        FALSE, hi.id, Idparent, level
                    FROM
                    (SELECT
                        HIERARCHY_CONNECT_BY_PARENT_EQ_PRIOR_ID(id) AS id,
                            @level AS level
                    FROM
                    (SELECT @start_with:=:message_id, @id:=@start_with, @level:=0) vars, messages
                    WHERE
                        @id IS NOT NULL) ho
                    JOIN messages hi ON hi.id = ho.id) q) q2
                ORDER BY level
            ');
            $stmt->execute([':message_id' => $message->getId()]);
            $ids = $stmt->fetchAll(PDO::FETCH_NUM);
            $ids = array_map(
                function ($value) {
                    return $value[0];
                },
                $ids
            );
            /** @var MessageRepository $repository */
            $repository = $this->getManager()->getRepository(Message::class);
            $result = $repository->findBy(
                ['id' => $ids],
                ['created' => 'DESC']
            );
        } catch (DBALException $e) {
        }

        return $result;
    }

    /**
     * Tests if a member has exceeded its limit for sending messages.
     *
     * @param Member $member
     * @param int    $perHour
     * @param int    $perDay
     *
     * @return bool
     */
    public function hasMessageLimitExceeded($member, $perHour, $perDay)
    {
        $id = $member->getId();

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
                    AND
                    (
                        Status = 'ToSend'
                        OR
                        Status = 'Sent'
                        AND
                        DateSent > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    )
                ) AS numberOfMessagesLastHour,
                (
                SELECT
                    COUNT(*)
                FROM
                    messages
                WHERE
                    messages.IdSender = :id
                    AND
                    (
                        Status = 'ToSend'
                        OR
                        Status = 'Sent'
                        AND
                        DateSent > DATE_SUB(NOW(), INTERVAL 1 DAY)
                    )
                ) AS numberOfMessagesLastDay
            ";
        $connection = $this->getManager()->getConnection();

        $row = null;
        try {
            $query = $connection->prepare($sql);
            $query->bindValue(':id', $id);

            $result = $query->execute();
            if ($result) {
                $row = $query->fetchAll(PDO::FETCH_OBJ);
            }
        } catch (DBALException $e) {
            return false;
        }

        if (null === $row) {
            return false;
        }

        $comments = $row[0]->numberOfComments;
        $lastHour = $row[0]->numberOfMessagesLastHour;
        $lastDay = $row[0]->numberOfMessagesLastDay;

        if (
            $comments < 1 && (
                $lastHour >= $perHour ||
                $lastDay >= $perDay)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Creates a new message and stores it into the database afterwards sends an notification to the receiver
     * Only used for messages therefore request is set to null!
     *
     * @param Member       $sender
     * @param Member       $receiver
     * @param Message|null $parent
     * @param $subjectText
     * @param $body
     *
     * @throws ORMException
     * @throws OptimisticLockException
     *
     * @return Message
     */
    public function addMessage(Member $sender, Member $receiver, ?Message $parent, $subjectText, $body)
    {
        $em = $this->getManager();
        $message = new Message();
        if (null === $parent) {
            $subject = new Subject();
            $subject->setSubject($subjectText);
            $em->persist($subject);
            $em->flush();
        } else {
            $subject = $parent->getSubject();
        }

        $message->setSubject($subject);
        $message->setSender($sender);
        $message->setRequest(null);
        $message->setParent($parent);
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setMessage($body);
        $message->setStatus('Sent');
        $em->persist($message);
        $em->flush();

        // \todo Send email notification
        $this->sendTemplateEmail($sender, $receiver, 'message', [
            'sender' => $sender,
            'receiver' => $receiver,
            'message' => $message,
            'subject' => $subjectText,
            'body' => $body,
        ]);

        return $message;
    }

    public function getThreadInformationForMessage(Message $hostingRequest)
    {
        $thread = $this->getThreadForMessage($hostingRequest);
        $first = $thread[\count($thread) - 1];
        $last = $thread[0];
        $guest = $first->getSender();
        $host = $first->getReceiver();

        return [$thread, $first, $last, $guest, $host];
    }
}
