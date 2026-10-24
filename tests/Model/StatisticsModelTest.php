<?php

namespace App\Tests\Model;

use App\Model\StatisticsModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatisticsModelTest extends TestCase
{
    public function testHomepageStatisticsAreQueriedOnceAndThenServedFromCache(): void
    {
        $result = $this->createStub(Result::class);
        $result->method('fetchOne')->willReturn(42);

        $connection = $this->createMock(Connection::class);
        $connection->expects($this->exactly(5))->method('executeQuery')->willReturn($result);

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
    }
}
