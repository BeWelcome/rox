<?php

namespace App\Utilities;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConversationThread
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Returns the conversation that contains the given message.
     *
     * @return Message[]
     */
    public function getThread(Message $conversation): array
    {
        $connection = $this->entityManager->getConnection();
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
        $result = $stmt->executeQuery([':message_id' => $conversation->getId()]);
        $ids = $result->fetchAllNumeric();
        $ids = array_map(
            function ($value) {
                return $value[0];
            },
            $ids
        );

        /** @var MessageRepository $repository */
        $repository = $this->entityManager->getRepository(Message::class);
        $result = $repository->findBy(
            ['id' => $ids],
            ['created' => 'DESC']
        );

        return $result;
    }
}
