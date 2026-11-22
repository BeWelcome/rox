<?php

namespace App\Tests\Command;

use App\Command\SendMassmailCommand;
use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Entity\Newsletter;
use App\Entity\NewsletterRecipient;
use App\Entity\NewsletterStatistics;
use App\Repository\NewsletterRepository;
use App\Repository\NewsletterStatisticsRepository;
use App\Service\Mailer;
use Doctrine\DBAL\Connection;
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

    public function testAMessageTheMailServerRefusesIsFrozen(): void
    {
        $newsletter = $this->createStub(Newsletter::class);
        $newsletter->method('getId')->willReturn(7);
        $newsletter->method('getType')->willReturn('Normal');
        $receiver = $this->createStub(Member::class);
        $receiver->method('getId')->willReturn(42);
        $receiver->method('getStatus')->willReturn(MemberStatusType::ACTIVE);
        $scheduled = $this->createStub(NewsletterRecipient::class);
        $scheduled->method('getNewsletter')->willReturn($newsletter);
        $scheduled->method('getReceiver')->willReturn($receiver);

        $messages = $this->createStub(EntityRepository::class);
        $messages->method('findBy')->willReturn([$scheduled]);
        $newsletters = $this->createStub(NewsletterRepository::class);
        $newsletters->method('getTranslations')->willReturn([]);
        $statistics = $this->createMock(NewsletterStatisticsRepository::class);
        $statistics->expects($this->once())->method('add')->with($newsletter, 0, -1, 0, 1);

        $statuses = [];
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchOne')->willReturn(1);
        $connection->method('executeStatement')->willReturnCallback(
            static function (string $sql, array $parameters) use (&$statuses): int {
                $statuses[] = $parameters['status'];

                return 1;
            }
        );

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager->method('getRepository')->willReturnMap([
            [NewsletterRecipient::class, $messages],
            [NewsletterStatistics::class, $statistics],
            [Newsletter::class, $newsletters],
        ]);

        $mailer = $this->createStub(Mailer::class);
        $mailer->method('sendNewsletterEmail')->willReturn(false);

        $tester = new CommandTester(new SendMassmailCommand($entityManager, $mailer, 25));

        self::assertSame(Command::SUCCESS, $tester->execute([]));
        self::assertSame(['Failed'], $statuses);
        self::assertStringContainsString('Sent 0 messages', $tester->getDisplay());
    }
}
