<?php

namespace App\Utilities;

use App\Entity\Message;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConversationThread
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Returns the message, the messages it replies to and all replies to it, newest first.
     */
    public function getThread(Message $conversation): array
    {
        // Walks up the parents and down the replies. UNION (not UNION ALL) stops at a message
        // that was already visited, so a broken parent chain that loops can't recurse forever.
        $ids = $this->entityManager->getConnection()->fetchFirstColumn(
            '
                WITH RECURSIVE
                    ancestors (id, parent) AS (
                        SELECT id, parent_id FROM message WHERE id = :message_id
                        UNION
                        SELECT m.id, m.parent_id FROM message m JOIN ancestors a ON m.id = a.parent
                    ),
                    replies (id) AS (
                        SELECT id FROM message WHERE id = :message_id
                        UNION
                        SELECT m.id FROM message m JOIN replies r ON m.parent_id = r.id
                    )
                SELECT id FROM ancestors
                UNION
                SELECT id FROM replies
            ',
            ['message_id' => $conversation->getId()]
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
