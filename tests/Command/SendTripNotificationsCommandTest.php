<?php

namespace App\Tests\Command;

use App\Command\SendTripNotificationsCommand;
use App\Entity\Preference;
use App\Model\TripModel;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SendTripNotificationsCommandTest extends TestCase
{
    public static function frequencyProvider(): Generator
    {
        yield 'daily' => ['daily', Preference::TRIP_NOTIFICATIONS_DAILY, 1];
        yield 'weekly' => ['weekly', Preference::TRIP_NOTIFICATIONS_WEEKLY, 7];
        yield 'monthly' => ['monthly', Preference::TRIP_NOTIFICATIONS_MONTHLY, 31];
    }

    #[DataProvider('frequencyProvider')]
    public function testExecuteSendsExpectedFrequency(string $argument, string $preference, int $days): void
    {
        $tripModel = $this->createMock(TripModel::class);
        $tripModel
            ->expects($this->once())
            ->method('sendScheduledTripNotifications')
            ->with(
                $preference,
                $this->callback(static function (DateTimeInterface $since) use ($days): bool {
                    $now = new DateTimeImmutable();

                    return $since <= $now->sub(new DateInterval('P' . $days . 'D'))->modify('+5 seconds')
                        && $since >= $now->sub(new DateInterval('P' . $days . 'D'))->modify('-5 seconds');
                }),
                $this->isInstanceOf(DateTimeInterface::class),
            )
            ->willReturn(3)
        ;

        $tester = new CommandTester(new SendTripNotificationsCommand($tripModel));

        $this->assertSame(Command::SUCCESS, $tester->execute(['frequency' => $argument]));
        $this->assertStringContainsString('Sent 3 trip notification emails.', $tester->getDisplay());
    }

    public function testExecuteRejectsInvalidFrequency(): void
    {
        $tripModel = $this->createMock(TripModel::class);
        $tripModel->expects($this->never())->method('sendScheduledTripNotifications');

        $tester = new CommandTester(new SendTripNotificationsCommand($tripModel));

        $this->assertSame(Command::INVALID, $tester->execute(['frequency' => 'yearly']));
        $this->assertStringContainsString('Frequency must be daily, weekly or monthly.', $tester->getDisplay());
    }
}
