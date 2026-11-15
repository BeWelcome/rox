<?php

namespace App\Tests\Model;

use App\Doctrine\CommentQualityType;
use App\Doctrine\MemberStatusType;
use App\Entity\Activity;
use App\Model\StatisticsModel;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatisticsModelTest extends TestCase
{
    public function testHomepageStatisticsAreQueriedOnceAndThenServedFromCache(): void
    {
        $queries = [];
        $parameters = [];
        // the countries in native SQL
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('fetchOne')
            ->with(
                $this->logicalAnd(
                    $this->stringContains('SELECT COUNT(DISTINCT l.country_id) FROM location l'),
                    $this->stringContains('SELECT DISTINCT a.location FROM address a INNER JOIN member m ON m.id = a.member_id'),
                    $this->stringContains('WHERE m.Status IN (:statuses)'),
                    $this->stringContains(') places ON places.location = l.geoname_id'),
                ),
                ['statuses' => [MemberStatusType::ACTIVE, MemberStatusType::OUT_OF_REMIND]],
                ['statuses' => ArrayParameterType::STRING],
            )
            ->willReturn('42')
        ;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $entityManager
            ->expects($this->exactly(4))
            ->method('createQuery')
            ->willReturnCallback(function (string $dql) use (&$queries, &$parameters): Query {
                $queries[] = $dql;
                $index = \count($queries) - 1;
                $query = $this->createStub(Query::class);
                $query->method('setParameter')->willReturnCallback(
                    static function (string $name, mixed $value) use (&$parameters, $index, &$query): Query {
                        $parameters[$index][$name] = $value;

                        return $query;
                    }
                );
                $query->method('getSingleScalarResult')->willReturn(42);

                return $query;
            })
        ;

        $model = new StatisticsModel(
            $this->createStub(TranslatorInterface::class),
            $entityManager,
            new ArrayAdapter(),
        );

        $expected = [
            'members' => 42,
            'countries' => 42,
            'languages' => 42,
            'comments' => 42,
            'activities' => 42,
        ];
        $this->assertSame($expected, $model->getStatisticsHomepage());
        $this->assertSame($expected, $model->getStatisticsHomepage());

        // members, languages and comments count the active members (countries those who can be found, above)
        $this->assertSame([
            'SELECT COUNT(m.id) FROM App\Entity\Member m WHERE m.status IN (:statuses)',
            'SELECT COUNT(DISTINCT IDENTITY(mll.language)) FROM App\Entity\MemberLanguageLevel mll'
                . ' WHERE IDENTITY(mll.member) IN (SELECT m.id FROM App\Entity\Member m WHERE m.status IN (:statuses))',
            'SELECT COUNT(c.id) FROM App\Entity\Comment c JOIN c.fromMember m'
                . ' WHERE c.quality = :positive AND m.status IN (:statuses)',
            'SELECT COUNT(a.id) FROM App\Entity\Activity a WHERE a.status = :active',
        ], $queries);
        $this->assertSame([
            ['statuses' => MemberStatusType::ACTIVE_ALL_ARRAY],
            ['statuses' => MemberStatusType::ACTIVE_ALL_ARRAY],
            ['positive' => CommentQualityType::POSITIVE, 'statuses' => MemberStatusType::ACTIVE_ALL_ARRAY],
            ['active' => Activity::STATUS_ACTIVE],
        ], $parameters);
    }

    public function testHomepageStatisticsAreCachedForAnHour(): void
    {
        $query = $this->createStub(Query::class);
        $query->method('setParameter')->willReturnSelf();
        $query->method('getSingleScalarResult')->willReturn(1);
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchOne')->willReturn('1');
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('createQuery')->willReturn($query);
        $entityManager->method('getConnection')->willReturn($connection);

        $item = $this->createMock(ItemInterface::class);
        $item->expects($this->once())->method('expiresAfter')->with(3600)->willReturnSelf();

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('get')
            ->with('homepage_statistics')
            ->willReturnCallback(static fn (string $key, callable $callback): mixed => $callback($item))
        ;

        $model = new StatisticsModel($this->createStub(TranslatorInterface::class), $entityManager, $cache);

        $this->assertSame(1, $model->getStatisticsHomepage()['members']);
    }
}
