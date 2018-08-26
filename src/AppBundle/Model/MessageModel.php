<?php

namespace AppBundle\Model;

use AppBundle\Doctrine\DeleteRequestType;
use AppBundle\Doctrine\InFolderType;
use AppBundle\Doctrine\MessageStatusType;
use AppBundle\Doctrine\SpamInfoType;
use AppBundle\Entity\Member;
use AppBundle\Entity\Message;
use AppBundle\Repository\MessageRepository;
use Doctrine\DBAL\DBALException;
use PDO;

/**
 * Class MessageModel.
 *
 * @SuppressWarnings(PHPMD.StaticAccess)
 * Hide logic in DeleteRequestType
 */
class MessageModel extends BaseModel
{
    /**
     * @param Member $member
     * @param array  $messageIds
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markDeleted(Member $member, array $messageIds)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

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
            $this->em->persist($message);
        }
        $this->em->flush();
    }

    /**
     * @param Member $member
     * @param array  $messageIds
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function unmarkDeleted(Member $member, array $messageIds)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

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
            $this->em->persist($message);
        }
        $this->em->flush();
    }

    /**
     * @param array $messageIds
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAsSpamByChecker(array $messageIds)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            $message
                ->setStatus(MessageStatusType::CHECKED)
                ->updateSpaminfo(SpamInfoType::CHECKER_SAYS_SPAM);
            $this->em->persist($message);
        }
        $this->em->flush();
    }

    /**
     * @param array $messageIds
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function unmarkAsSpamByChecker(array $messageIds)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

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
            $this->em->persist($message);
        }
        $this->em->flush();
    }

    /**
     * @param array $messageIds
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function markAsSpam(array $messageIds)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            $message
                ->setInFolder(InFolderType::SPAM)
                ->setStatus(MessageStatusType::CHECK)
                ->updateSpaminfo(SpamInfoType::MEMBER_SAYS_SPAM);
            $this->em->persist($message);
        }
        $this->em->flush();
    }

    /**
     * @param array $messageIds
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function unmarkAsSpam(array $messageIds)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

        $messages = $repository->findBy([
            'id' => $messageIds,
        ]);

        /** @var Message $message */
        foreach ($messages as $message) {
            $message
                ->setInFolder(InFolderType::NORMAL)
                ->setStatus(MessageStatusType::CHECKED)
                ->setSpaminfo(SpamInfoType::NO_SPAM);
            $this->em->persist($message);
        }
        $this->em->flush();
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
        $repository = $this->em->getRepository(Message::class);

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
     * @return \Pagerfanta\Pagerfanta
     */
    public function getFilteredMessages($member, $folder, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

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
     * @return \Pagerfanta\Pagerfanta
     */
    public function getFilteredRequests($member, $folder, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

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
     * @return \Pagerfanta\Pagerfanta
     */
    public function getFilteredRequestsAndMessages($member, $folder, $sort, $sortDir, $page = 1, $limit = 10)
    {
        /** @var MessageRepository $repository */
        $repository = $this->em->getRepository(Message::class);

        return $repository->findLatestRequestsAndMessages($member, $folder, $sort, $sortDir, $page, $limit);
    }

    /**
     * Returns the thread that contains the given message.
     *
     * @param Message $message
     *
     * @throws \Doctrine\DBAL\DBALException
     *
     * @return Message[]
     */
    public function getThreadForMessage(Message $message)
    {
        $connection = $this->em->getConnection();
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
        $repository = $this->em->getRepository(Message::class);
        $result = $repository->findBy(
            [
            'id' => $ids,
            ],
            ['created' => 'DESC']
        );

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
        $connection = $this->em->getConnection();

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

        if ($comments < 1 && (
                $lastHour >= $perHour ||
                $lastDay >= $perDay)) {
            return true;
        }

        return false;
    }
}
