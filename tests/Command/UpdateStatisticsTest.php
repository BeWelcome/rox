<?php

namespace App\Tests\Command;

use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * statistics:update: the given last date is included; a single day is updated too.
 */
#[Group('integration')]
class UpdateStatisticsTest extends KernelTestCase
{
    public function testASingleDayIsUpdated(): void
    {
        self::assertSame(['2026-08-05'], $this->update(['startDate' => '2026-08-05', 'endDate' => '2026-08-05']));
    }

    public function testTheLastDateIsIncluded(): void
    {
        self::assertSame(
            ['2026-08-05', '2026-08-06', '2026-08-07'],
            $this->update(['startDate' => '2026-08-05', 'endDate' => '2026-08-07']),
        );
    }

    public function testWithoutALastDateTheUpdateEndsWithYesterday(): void
    {
        $start = new DateTimeImmutable('-2 days');

        self::assertSame(
            [$start->format('Y-m-d'), $start->modify('+1 day')->format('Y-m-d')],
            $this->update(['startDate' => $start->format('Y-m-d')]),
        );
    }

    /**
     * @param array<string, string> $arguments
     *
     * @return list<string> the days the stats table has afterwards
     */
    private function update(array $arguments): array
    {
        self::bootKernel();
        $connection = static::getContainer()->get(Connection::class);
        $connection->executeStatement('DELETE FROM stats');
        $tester = new CommandTester(new Application(self::$kernel)->find('statistics:update'));

        self::assertSame(Command::SUCCESS, $tester->execute($arguments));

        return array_map(
            static fn (string $day): string => substr($day, 0, 10),
            $connection->fetchFirstColumn('SELECT created FROM stats ORDER BY created'),
        );
    }
}
