<?php

namespace App\Tests\Utilities;

use App\Entity\Message;
use App\Utilities\ConversationThread;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('integration')]
class ConversationThreadTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private ConversationThread $conversationThread;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        $this->conversationThread = new ConversationThread($this->entityManager);
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function testThreadContainsTheMessageItsParentsAndAllRepliesNewestFirst(): void
    {
        $parents = $this->parents();
        self::assertNotEmpty(array_filter($parents), 'the test fixtures contain replies');

        foreach (array_keys($parents) as $id) {
            $thread = $this->conversationThread->getThread($this->entityManager->find(Message::class, $id));

            $ids = array_map(static fn (Message $message): int => $message->getId(), $thread);
            sort($ids);
            self::assertSame($this->expectedThread($id, $parents), $ids, "thread of message $id");

            $created = array_map(static fn (Message $message): int => $message->getCreated()->getTimestamp(), $thread);
            $newestFirst = $created;
            rsort($newestFirst);
            self::assertSame($newestFirst, $created, "thread of message $id is newest first");
        }
    }

    public function testParentChainThatLoopsEndsInsteadOfRecursingForever(): void
    {
        $parents = array_filter($this->parents());
        // take the last reply of a chain: a message nothing replies to
        $lastReplies = array_diff(array_keys($parents), $parents);
        self::assertNotEmpty($lastReplies, 'the test fixtures contain replies');
        $reply = reset($lastReplies);
        $parent = $parents[$reply];

        // let the parent reply to its own reply: parent -> reply -> parent
        $this->connection->executeStatement('UPDATE message SET parent_id = ? WHERE id = ?', [$reply, $parent]);
        $this->entityManager->clear();

        $thread = $this->conversationThread->getThread($this->entityManager->find(Message::class, $reply));

        $ids = array_map(static fn (Message $message): int => $message->getId(), $thread);
        self::assertContains($reply, $ids);
        self::assertContains($parent, $ids);
        self::assertSame($ids, array_values(array_unique($ids)));
    }

    /**
     * @return array<int, int|null> parent id by message id
     */
    private function parents(): array
    {
        $parents = [];
        foreach ($this->connection->fetchAllNumeric('SELECT id, parent_id FROM message') as [$id, $parent]) {
            $parents[(int) $id] = $parent ? (int) $parent : null;
        }

        return $parents;
    }

    /**
     * @param array<int, int|null> $parents
     *
     * @return list<int> the message, all its parents and all replies to it, sorted
     */
    private function expectedThread(int $id, array $parents): array
    {
        $thread = [$id];
        for ($parent = $parents[$id]; null !== $parent && !\in_array($parent, $thread, true); $parent = $parents[$parent] ?? null) {
            $thread[] = $parent;
        }

        $open = [$id];
        while ($open) {
            $current = array_shift($open);
            foreach (array_keys($parents, $current, true) as $reply) {
                if (!\in_array($reply, $thread, true)) {
                    $thread[] = $reply;
                    $open[] = $reply;
                }
            }
        }
        sort($thread);

        return $thread;
    }
}
