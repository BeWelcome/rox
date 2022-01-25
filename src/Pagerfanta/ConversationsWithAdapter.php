<?php

namespace App\Pagerfanta;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\MessageResultSetMapping;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\AdapterInterface;
use PDO;

class ConversationsWithAdapter implements AdapterInterface
{
    private Member $member;
    private Member $partner;
    private Connection $connection;
    private EntityManager $entityManager;

    public function __construct(
        EntityManager $entityManager,
        Member $member,
        Member $partner
    ) {
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
        $this->partner = $partner;
        $this->member = $member;
    }

    private function getConversationsQuery(): string
    {
        return '
            SELECT `m`.*
            FROM `messages` m
            WHERE
				NOT m.subject_id IS NULL
				AND m.id IN (
					SELECT max(m.id)
					FROM messages m
                    WHERE
						((m.IdReceiver = :memberId AND m.IdSender = :partnerId) OR
						(m.IdReceiver = :partnerId AND m.IdSender = :memberId))
					GROUP BY m.subject_id
				)
			UNION
            SELECT `m`.*
            FROM `messages` m
            WHERE
				m.subject_id is null
				AND	((m.IdReceiver = :memberId AND m.IdSender = :partnerId) OR
					(m.IdReceiver = :partnerId AND m.IdSender = :memberId))
        ';
    }

    public function getNbResults(): int
    {
        $count = 0;
        try {
            $countQuery = 'SELECT count(*) FROM (' . $this->getConversationsQuery() . ') m';
            $result = $this->connection->executeQuery(
                $countQuery,
                [
                    ':memberId' => $this->member->getId(),
                    ':partnerId' => $this->partner->getId(),
                ],
                [
                    \PDO::PARAM_INT,
                    \PDO::PARAM_INT,
                ]
            );
            $count = $result->fetchOne();
        } catch (DBALException $e) {
            // Return 0
        }

        return $count;
    }

    public function getSlice(int $offset, int $length): iterable
    {
        $sql = 'SELECT * FROM (' . $this->getConversationsQuery() . ') m';
        $sql .= ' ORDER BY `m`.`created` DESC LIMIT ' . $length . ' OFFSET ' . $offset;

        $query = $this->entityManager->createNativeQuery($sql, new MessageResultSetMapping())
            ->setParameter(':memberId', $this->member->getId())
            ->setParameter(':partnerId', $this->partner->getId())
        ;

        $conversations = $query->getResult();

        return $conversations;
    }
}
