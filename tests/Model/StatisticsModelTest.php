<?php

namespace App\Tests\Model;

use App\Doctrine\MemberStatusType;
use App\Model\StatisticsModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatisticsModelTest extends TestCase
{
    public function testHomepageStatisticsAreQueriedOnceAndThenServedFromCache(): void
    {
        $result = $this->createStub(Result::class);
        $result->method('fetchOne')->willReturn(42);

        $queries = [];
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->exactly(5))
            ->method('executeQuery')
            ->willReturnCallback(static function (string $sql) use (&$queries, $result): Result {
                $queries[] = preg_replace('/\s+/', ' ', $sql);

                return $result;
            })
        ;

        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);

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

        // members, languages and comments only count active members
        $activeOnly = '.status IN (' . MemberStatusType::ACTIVE_ALL . ') ';
        $this->assertCount(5, $queries);
        foreach ([0, 2, 3] as $index) {
            $this->assertStringContainsStringIgnoringCase($activeOnly, $queries[$index]);
        }
    }

    public function testHomepageStatisticsAreCachedForAnHour(): void
    {
        $result = $this->createStub(Result::class);
        $result->method('fetchOne')->willReturn(1);
        $connection = $this->createStub(Connection::class);
        $connection->method('executeQuery')->willReturn($result);
        $entityManager = $this->createStub(EntityManagerInterface::class);
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
