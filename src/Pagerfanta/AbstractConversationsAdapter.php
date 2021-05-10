<?php

namespace App\Pagerfanta;

use App\Doctrine\DeleteRequestType;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

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
            $sql = $this->getSqlCountQuery();
            $stmt = $this->connection->executeQuery($sql, [':memberId' => $this->member->getId()], [\PDO::PARAM_INT]);
            $row = $stmt->fetchAll(\PDO::FETCH_OBJ);
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
     * @return array|Traversable the slice
     */
    public function getSlice($offset, $length)
    {
        $sql = $this->getSqlQuery();
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
        $rsm->addMetaResult('m', 'initiator_id', 'initiator_id');
        $rsm->addMetaResult('m', 'subject_id', 'subject_id');
        $rsm->addMetaResult('m', 'request_id', 'request_id');

        $query = $this->entityManager->createNativeQuery($sql, $rsm)
            ->setParameter(':memberId', $this->member->getId())
        ;

        $conversations = $query->getResult();

        return $conversations;
    }

    protected function getSqlCountQuery(): string
    {
        $sql = str_replace('%select%', 'count(m.id) as count', $this->getSqlQueryTemplate());

        return $sql;
    }

    protected function getSqlQuery(): string
    {
        $sql = str_replace('%select%', '`m`.*', $this->getSqlQueryTemplate());

        return $sql;
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
            '(' .
                'm.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_DELETED . '%\'' .
                ' AND m.DeleteRequest NOT LIKE \'%' . DeleteRequestType::RECEIVER_PURGED . '%\'' .
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

    abstract protected function getSqlQueryTemplate(): string;
}
