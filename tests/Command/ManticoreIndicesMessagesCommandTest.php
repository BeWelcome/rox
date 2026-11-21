<?php

namespace App\Tests\Command;

use App\Command\ManticoreIndicesMessagesCommand;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Manticoresearch\Client;
use Manticoresearch\Table;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * The message index gets all messages without Manticore's auto-optimize, then its disk chunks are merged once, and
 * auto-optimize is on again afterwards, also when the insert fails. The documents are replaced, not inserted: on the
 * live site the listener may have indexed a message before the build gets to it.
 */
class ManticoreIndicesMessagesCommandTest extends TestCase
{
    /** @var list<string> what reached Manticore, in order */
    private array $calls = [];

    public function testTheIndexIsFilledWithoutAutoOptimizeAndMergedOnce(): void
    {
        $tester = new CommandTester($this->command(fail: false));

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertSame([
            "SHOW TABLES LIKE 'message_rt'",
            'create',
            'SET GLOBAL auto_optimize=0',
            'replace 2',
            'replace 1',
            'OPTIMIZE TABLE message_rt OPTION cutoff=1, sync=1',
            'SET GLOBAL auto_optimize=1',
        ], $this->calls);
    }

    public function testAutoOptimizeIsOnAgainWhenTheInsertFails(): void
    {
        $tester = new CommandTester($this->command(fail: true));

        try {
            $tester->execute([]);
            self::fail('the failed insert was swallowed');
        } catch (RuntimeException) {
        }
        self::assertSame('SET GLOBAL auto_optimize=1', end($this->calls));
        self::assertNotContains('OPTIMIZE TABLE message_rt OPTION cutoff=1, sync=1', $this->calls);
    }

    private function command(bool $fail): ManticoreIndicesMessagesCommand
    {
        $calls = &$this->calls;

        $table = $this->createStub(Table::class);
        $table->method('create')->willReturnCallback(static function () use (&$calls) {
            $calls[] = 'create';

            return [];
        });
        $table->method('replaceDocuments')->willReturnCallback(static function (array $documents) use (&$calls, $fail) {
            if ($fail) {
                throw new RuntimeException('Manticore closed the connection');
            }
            $calls[] = 'replace ' . \count($documents);

            return [];
        });

        $client = $this->createStub(Client::class);
        $client->method('table')->willReturn($table);
        $client->method('sql')->willReturnCallback(static function (string $statement) use (&$calls) {
            $calls[] = $statement;

            return [];
        });

        // two chunks: two messages, then one (the command reads 2500 at a time; the stub hands out what it has)
        $repository = $this->createStub(MessageRepository::class);
        $repository->method('countSearchIndexDocuments')->willReturn(3);
        $repository->method('getSearchIndexDocuments')->willReturnOnConsecutiveCalls(
            [['id' => 1], ['id' => 2]],
            [['id' => 3]],
            [],
        );
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        return new ManticoreIndicesMessagesCommandWithClient($entityManager, $client);
    }
}
