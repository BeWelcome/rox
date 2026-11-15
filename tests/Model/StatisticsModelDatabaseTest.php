<?php

namespace App\Tests\Model;

use App\Doctrine\MemberStatusType;
use App\Entity\Statistic;
use App\Model\StatisticsModel;
use DateInterval;
use DatePeriod;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\NullOutput;

/**
 * The statistics queries against the tables of the mapping (member, comment, language, ...), not the legacy
 * ones main still uses (members, comments, languages, ...).
 */
#[Group('integration')]
class StatisticsModelDatabaseTest extends KernelTestCase
{
    private StatisticsModel $model;
    private Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->model = static::getContainer()->get(StatisticsModel::class);
        $this->connection = static::getContainer()->get(Connection::class);
        // the numbers are cached for an hour; the database is rolled back after each test, the cache isn't
        $cache = static::getContainer()->get('cache.app');
        $keys = [
            'homepage_statistics',
            'statistics_languages',
            'statistics_preferred_languages',
            'statistics_members_per_country',
            'statistics_logins',
        ];
        foreach ($keys as $key) {
            $cache->delete($key);
        }
    }

    public function testUpdateStatisticsCountsFromTheMemberAndCommentTables(): void
    {
        $today = new DateTime('today');
        $member = (int) $this->connection->fetchOne("SELECT id FROM member WHERE Status = 'Active' ORDER BY id LIMIT 1");
        $this->connection->executeStatement('UPDATE member SET LastActive = ? WHERE id = ?', [$today->format('Y-m-d 10:00:00'), $member]);
        $this->connection->executeStatement('DELETE FROM stats');
        // The statistics of a day count the members created before it starts
        $this->connection->executeStatement('UPDATE member SET created = ? WHERE id <> ?', [$today->format('Y-m-d 00:00:00'), $member]);
        $this->connection->executeStatement('UPDATE member SET created = ? WHERE id = ?', [$today->format('Y-m-d 08:00:00'), $member]);
        $this->connection->executeStatement('UPDATE comment SET updated = ?', [$today->format('Y-m-d 00:00:00')]);

        $this->model->updateStatistics(new DatePeriod($today, new DateInterval('P1D'), new DateTime('tomorrow')), new NullOutput());

        $statistic = static::getContainer()->get(EntityManagerInterface::class)
            ->getRepository(Statistic::class)->findOneBy(['created' => $today]);
        self::assertNotNull($statistic);
        self::assertSame(
            (int) $this->connection->fetchOne("SELECT COUNT(*) FROM member WHERE Status IN ('Active','ChoiceInactive','OutOfRemind')") - 1,
            (int) $statistic->getActiveMembers()
        );
        self::assertGreaterThan(0, (int) $statistic->getActiveMembers());
        self::assertSame(
            (int) $this->connection->fetchOne("SELECT COUNT(DISTINCT c.to_member_id) FROM comment c JOIN member m ON m.id = c.to_member_id
                WHERE c.quality = 'positive' AND m.Status IN ('Active','ChoiceInactive','OutOfRemind')"),
            (int) $statistic->getMembersWithPositiveComment()
        );
        self::assertGreaterThan(0, (int) $statistic->getMembersWithPositiveComment());
        self::assertGreaterThanOrEqual(1, (int) $statistic->getMembersWhoLoggedInToday());
    }

    public function testChartsCountActiveMembers(): void
    {
        $active = (int) $this->connection->fetchOne('SELECT COUNT(*) FROM member WHERE Status IN (' . MemberStatusType::ACTIVE_ALL . ')');

        self::assertSame($active, array_sum($this->model->getPreferredLanguagesData()));
        self::assertNotEmpty($this->model->getLanguagesData());
        self::assertNotEmpty($this->model->getMembersPerCountryData());

        $this->connection->executeStatement('UPDATE member SET LastActive = NOW() - INTERVAL 3 DAY');
        $perLogin = $this->model->getMembersPerLoginData();
        self::assertSame($active, array_sum($perLogin));
    }

    public function testMembersActiveInTheLastDayAreInTheFirstPeriod(): void
    {
        $this->connection->executeStatement('UPDATE member SET LastActive = NOW() - INTERVAL 3 DAY');
        // less than 24 hours ago: 0 days
        $this->connection->executeStatement(
            "UPDATE member SET LastActive = NOW() - INTERVAL 2 HOUR WHERE Status = 'Active' ORDER BY id LIMIT 1"
        );

        $perLogin = array_values($this->model->getMembersPerLoginData());

        // 1 day, 1 week, ...
        self::assertSame(1, $perLogin[0]);
    }

    public function testTheChartsAreCached(): void
    {
        $before = $this->model->getPreferredLanguagesData();
        $this->connection->executeStatement(
            "UPDATE member SET Status = 'Banned' WHERE Status = 'Active' ORDER BY id LIMIT 1"
        );

        self::assertSame($before, $this->model->getPreferredLanguagesData());
    }

    public function testHomepageCountsPositiveCommentsOfActiveMembers(): void
    {
        $statistics = $this->model->getStatisticsHomepage();

        self::assertSame(
            (int) $this->connection->fetchOne("SELECT COUNT(*) FROM comment c JOIN member m ON m.id = c.from_member_id
                WHERE c.quality = 'positive' AND m.Status IN (" . MemberStatusType::ACTIVE_ALL . ')'),
            (int) $statistics['comments']
        );
        self::assertGreaterThan(0, (int) $statistics['comments']);
    }
}
