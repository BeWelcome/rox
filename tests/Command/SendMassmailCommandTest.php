<?php

namespace App\Tests\Command;

use App\Command\SendMassmailCommand;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * The crontabs call "send:massmail 100": the batch size can be given as argument, as with main's command;
 * without it the configured size applies.
 */
class SendMassmailCommandTest extends TestCase
{
    public static function batchSizes(): iterable
    {
        yield 'as the crontabs call it' => [['batchSize' => '100'], 100];
        yield 'without argument' => [[], 25];
    }

    /**
     * @param array<string, string> $arguments
     */
    #[DataProvider('batchSizes')]
    public function testTheBatchSize(array $arguments, int $expected): void
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->expects($this->once())
            ->method('findBy')
            ->with(['status' => 'ToSend'], ['updated' => 'ASC'], $expected, 0)
            ->willReturn([]);
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $tester = new CommandTester(new SendMassmailCommand($entityManager, $this->createStub(Mailer::class), 25));

        self::assertSame(Command::SUCCESS, $tester->execute($arguments));
    }
}
