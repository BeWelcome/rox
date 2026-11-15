<?php

namespace App\Tests\Model;

use App\Doctrine\MemberStatusType;
use App\Model\StatisticsModel;
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
                $this->stringContains('COUNT(DISTINCT l.country_id)'),
                ['statuses' => [MemberStatusType::ACTIVE, MemberStatusType::OUT_OF_REMIND]],
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
        $this->assertCount(4, $queries);
        foreach ([0, 1, 2] as $index) {
            $this->assertStringContainsString('m.status IN (:statuses)', $queries[$index]);
            $this->assertSame(MemberStatusType::ACTIVE_ALL_ARRAY, $parameters[$index]['statuses']);
        }
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
