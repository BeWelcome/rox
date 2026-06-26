<?php

namespace App\Tests\Repository;

use App\Entity\BrowserPushNotification;
use App\Repository\BrowserPushNotificationRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\TestCase;

class BrowserPushNotificationRepositoryTest extends TestCase
{
    public function testReleaseStaleProcessingNotificationsMakesRowsRetryable(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('executeStatement')
            ->with(
                self::logicalAnd(
                    self::stringContains('UPDATE browser_push_notification'),
                    self::stringContains('status = ?'),
                    self::stringContains('updated < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL ? MINUTE)')
                ),
                [
                    BrowserPushNotification::STATUS_SCHEDULED,
                    BrowserPushNotification::STATUS_PROCESSING,
                    15,
                ],
                [ParameterType::STRING, ParameterType::STRING, ParameterType::INTEGER]
            )
            ->willReturn(2)
        ;
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $repository = new BrowserPushNotificationRepository(
            $entityManager,
            new ClassMetadata(BrowserPushNotification::class)
        );

        self::assertSame(2, $repository->releaseStaleProcessingNotifications());
    }
}
