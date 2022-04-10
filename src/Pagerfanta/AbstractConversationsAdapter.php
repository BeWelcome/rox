<?php

namespace App\Pagerfanta;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\MessageResultSetMapping;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;

abstract class AbstractConversationsAdapter
{
    protected Member $member;
    protected Connection $connection;
    protected EntityManager $entityManager;
    protected bool $unreadOnly;
    protected int $initiator;

    public function __construct(
        EntityManager $entityManager,
        Member $member,
        int $initiator,
        bool $unreadOnly
    ) {
        $this->connection = $entityManager->getConnection();
        $this->member = $member;
        $this->entityManager = $entityManager;
        $this->unreadOnly = $unreadOnly;
        $this->initiator = $initiator;
    }

    /**
     * Returns the number of results.
     */
    public function getNbResults(): int
    {
        $count = 0;
        try {
            $countQuery = 'SELECT count(*) FROM (' . $this->getConversationsQuery() . ') m';
            $result = $this->connection->executeQuery(
                $countQuery,
                [':memberId' => $this->member->getId()],
                [\PDO::PARAM_INT]
            );
            $count = $result->fetchOne();
        } catch (DBALException $e) {
            // Return 0
        }

        return $count;
    }

    /**
     * Returns a slice of the results.
     */
    public function getSlice(int $offset, int $length): iterable
    {
        $sql = 'SELECT * FROM (' . $this->getConversationsQuery() . ') m';
        $sql .= ' ORDER BY `m`.`created` DESC LIMIT ' . $length . ' OFFSET ' . $offset;

        $query = $this->entityManager->createNativeQuery($sql, new MessageResultSetMapping())
            ->setParameter(':memberId', $this->member->getId())
        ;

        $conversations = $query->getResult();

        return $conversations;
    }

    protected function getUnreadCondition(): string
    {
        if ($this->unreadOnly) {
            $unreadCondition = '(m.IdReceiver = :memberId) '
                . 'AND (`m`.WhenFirstRead IS NULL OR `m`.WhenFirstRead = \'0000-00-00 00:00:00\')';
        } else {
            $unreadCondition = '(m.IdReceiver = :memberId OR m.IdSender = :memberId)';
        }

        return $unreadCondition;
    }

    protected function getNotDeletedOrPurgedCondition(): string
    {
        $notDeletedOrPurgedCondition =
            '((m.IdReceiver = :memberId) OR (m.IdSender = :memberId)) AND (' .
                '((m.IdReceiver = :memberId) AND ' .
                    'm.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_DELETED . '%\'' .
                    ' AND m.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_PURGED . '%\'' .
                ') OR ((m.IdSender = :memberId) AND ' .
                    'm.DeleteRequest NOT LIKE \'%' . DeleteRequestType::SENDER_DELETED . '%\'' .
                    ' AND m.DeleteRequest NOT LIKE \'%' . DeleteRequestType::SENDER_PURGED . '%\'' .
                ')' .
            ')';

        return $notDeletedOrPurgedCondition;
    }

    protected function getInitiatorCondition(): string
    {
        $showStartedByMember = 3 !== $this->initiator;
        $showStartedByOther = 1 !== $this->initiator;
        if (!$showStartedByMember || !$showStartedByOther) {
            $initiatorCondition = '';
            if ($showStartedByMember) {
                $initiatorCondition = '(m.initiator_id = :memberId)';
                if ($showStartedByOther) {
                    $initiatorCondition .= ' AND ';
                }
            }
            if ($showStartedByOther) {
                $initiatorCondition .= '(m.initiator_id <> :memberId)';
            }
        } else {
            $initiatorCondition = '1 = 1';
        }

        return $initiatorCondition;
    }

    protected function getNotSpamCondition(): string
    {
        return 'InFolder <> \'' . InFolderType::SPAM . '\'';
    }

    abstract protected function getConversationsQuery(): string;
}
