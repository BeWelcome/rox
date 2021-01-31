<?php

namespace App\Pagerfanta;

use App\Entity\Member;
use App\Entity\Message;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class ConversationAdapter implements AdapterInterface
{
    public const MESSAGES = 1;
    public const REQUESTS = 2;
    public const INVITATIONS = 4;

    private Member $member;
    private Connection $connection;
    private EntityManager $entityManager;
    private bool $unreadOnly;
    private int $types;

    public function __construct(EntityManager $entityManager, Member $member, bool $unreadOnly, int $types)
    {
        $this->connection = $entityManager->getConnection();
        $this->member = $member;
        $this->entityManager = $entityManager;
        $this->unreadOnly = $unreadOnly;
        $this->types = $types;
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        $count = 0;
        try {
            $sql = $this->getSqlQuery(true);
            $stmt = $this->connection->executeQuery($sql,[':memberId' => $this->member->getId()], [PDO::PARAM_INT]);
            $row = $stmt->fetchAll(PDO::FETCH_OBJ);
            $count = ($row[0])->count;
        } catch (DBALException $e) {
            // Return 0
        }

        return $count;
    }

    /**
     * Returns an slice of the results.
     *
     * @param int $offset the offset
     * @param int $length the length
     *
     * @return array|\Traversable the slice
     */
    public function getSlice($offset, $length)
    {
        $sql = $this->getSqlQuery(false);
        $sql .= ' ORDER BY `m`.`created` DESC LIMIT ' . $length . ' OFFSET ' . $offset;
        $rsm = new ResultSetMapping();
        $rsm->addEntityResult('App:Message', 'm');
        $rsm->addFieldResult('m', 'id', 'id');
        $rsm->addFieldResult('m', 'Message', 'message');
        $rsm->addFieldResult('m', 'created', 'created');
        $rsm->addFieldResult('m', 'updated', 'updated');
        $rsm->addFieldResult('m', 'DateSent', 'dateSent');
        $rsm->addFieldResult('m', 'WhenFirstRead', 'firstRead');
        $rsm->addFieldResult('m', 'DeleteRequest', 'deleteRequest');
        $rsm->addMetaResult('m', 'IdParent', 'idParent');
        $rsm->addMetaResult('m', 'IdReceiver', 'idReceiver');
        $rsm->addMetaResult('m', 'IdSender', 'idSender');
        $rsm->addMetaResult('m', 'subject_id', 'subject_id');
        $rsm->addMetaResult('m', 'request_id', 'request_id');

        $query = $this->entityManager->createNativeQuery($sql, $rsm)
            ->setParameter(':memberId', $this->member->getId())
        ;

        $conversations = $query->getResult();
        // $stmt = $this->connection->executeQuery($sql, $params, $paramTypes);
        // $results = $stmt->fetchAll(PDO::FETCH_OBJ);

        return $conversations;
    }

    private function getSqlQuery($count): string
    {
        $messages = self::MESSAGES === ($this->types & self::MESSAGES);
        $requests = self::REQUESTS === ($this->types & self::REQUESTS);
        $invitations = self::INVITATIONS === ($this->types & self::INVITATIONS);

        $sql = null;
        if (!$messages && !$requests && !$invitations) {
            $sql = $this->getAllConversations();
        }
        if ($messages && !$requests && !$invitations) {
            $sql = $this->getMessagesOnly();
        }
        if (!$messages && $requests && !$invitations) {
            $sql = $this->getRequestsOnly();
        }
        if (!$messages && !$requests && $invitations) {
            $sql = $this->getInvitationsOnly();
        }
        if ($messages && !$requests && $invitations) {
            $sql = $this->getMessagesAndInvitations();
        }
        if ($messages && $requests && !$invitations) {
            $sql = $this->getMessagesAndRequests();
        }
        if (!$messages && $requests && $invitations) {
            $sql = $this->getRequestsAndInvitations();
        }
        if ($messages && $requests && $invitations) {
            $sql = $this->getAllConversations();
        }

        if ($this->unreadOnly) {
            $condition = '(m.IdReceiver = :memberId) '
                . 'AND (`m`.WhenFirstRead IS NULL OR `m`.WhenFirstRead = \'0000-00-00 00:00:00\')';
        } else {
            $condition = '(m.IdReceiver = :memberId OR m.IdSender = :memberId)';
        }
        $sql = str_replace('%unread_condition%', $condition, $sql);

        if ($count) {
            $sql = str_replace('%select%', 'count(m.id) as count', $sql);
        } else {
            $sql = str_replace('%select%', '`m`.*', $sql);
        }

        return $sql;
    }

    private function getAllConversations(): string
    {
        return '
            SELECT %select%
            FROM `messages` m
            WHERE
                %unread_condition%
                AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    GROUP BY `m`.`subject_id`
                )
         ';
    }

    private function getMessagesOnly(): string
    {
        return '
            SELECT %select%
            FROM `messages` m
            WHERE
                %unread_condition%
                AND `m`.`request_id` IS NULL
                AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    GROUP BY `m`.`subject_id`
                )
         ';
    }

    private function getRequestsOnly(): string
    {
        return '
            SELECT %select%
            FROM `messages` m, `request` r
            WHERE
                %unread_condition%
                AND `m`.`request_id` = `r`.`id`
                AND `r`.`invite_for_leg` IS NULL
                AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    GROUP BY `m`.`subject_id`
                )
         ';
    }

    private function getInvitationsOnly(): string
    {
        return '
            SELECT %select%
            FROM `messages` m
            LEFT JOIN `request` r ON `m`.`request_id` = `r`.`id`
            WHERE
                %unread_condition%
                AND `r`.`invite_for_leg` IS NOT NULL
                AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    GROUP BY `m`.`subject_id`
                )
         ';
    }

    private function getMessagesAndRequests(): string
    {
        return '
            SELECT %select%
            FROM `messages` m
            LEFT JOIN `request` r ON `m`.`request_id` = `r`.`id`
            WHERE
                %unread_condition%
                AND `r`.`invite_for_leg` IS NULL
                AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    GROUP BY `m`.`subject_id`
                )
         ';
    }

    private function getMessagesAndInvitations(): string
    {
        return '
            SELECT %select%
            FROM `messages` m
            LEFT JOIN `request` r ON `m`.`request_id` = `r`.`id`
            WHERE
                %unread_condition%
                AND (`m`.`request_id` IS NULL OR `r`.`invite_for_leg` IS NOT NULL)
                AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    GROUP BY `m`.`subject_id`
                )
         ';
    }

    private function getRequestsAndInvitations(): string
    {
        return '
            SELECT %select%
            FROM `messages` m
            WHERE
                %unread_condition%
                AND `m`.`request_id` IS NOT NULL
                AND `m`.`id` IN (
                    SELECT max(`m`.`id`)
                    FROM `messages` m
                    GROUP BY `m`.`subject_id`
                )
         ';
    }

}
