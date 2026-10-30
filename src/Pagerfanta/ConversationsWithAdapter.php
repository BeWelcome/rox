<?php

namespace App\Pagerfanta;

use App\Doctrine\MessageResultSetMapping;
use App\Entity\Member;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\AdapterInterface;

class ConversationsWithAdapter implements AdapterInterface
{
    private readonly Connection $connection;

    public function __construct(
        private readonly EntityManager $entityManager,
        private readonly Member $member,
        private readonly Member $partner,
    ) {
        $this->connection = $this->entityManager->getConnection();
    }

    public function getNbResults(): int
    {
        $countQuery = 'SELECT count(*) FROM (' . $this->getConversationsQuery() . ') m';
        $result = $this->connection->executeQuery(
            $countQuery,
            [
                'memberId' => $this->member->getId(),
                'partnerId' => $this->partner->getId(),
            ],
            [
                'memberId' => ParameterType::INTEGER,
                'partnerId' => ParameterType::INTEGER,
            ]
        );
        $count = $result->fetchOne();

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

    private function getConversationsQuery(): string
    {
        return '
            SELECT `m`.*
            FROM `message` m
            WHERE
				NOT m.subject_id IS NULL
				AND m.id IN (
					SELECT max(m.id)
					FROM message m
                    WHERE
						((m.receiver_id = :memberId AND m.sender_id = :partnerId) OR
						(m.receiver_id = :partnerId AND m.sender_id = :memberId))
					GROUP BY m.subject_id
				)
			UNION
            SELECT `m`.*
            FROM `message` m
            WHERE
				m.subject_id is null
				AND	((m.receiver_id = :memberId AND m.sender_id = :partnerId) OR
					(m.receiver_id = :partnerId AND m.sender_id = :memberId))
        ';
    }
}
