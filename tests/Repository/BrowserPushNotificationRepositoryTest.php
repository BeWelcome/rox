<?php

namespace App\Tests\Repository;

use App\Entity\BrowserPushNotification;
use App\Repository\BrowserPushNotificationRepository;
use Doctrine\DBAL\ArrayParameterType;
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
                    30,
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

    public function testRenewsProcessingLeaseForClaimedRows(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('executeStatement')
            ->with(
                self::logicalAnd(
                    self::stringContains('UPDATE browser_push_notification'),
                    self::stringContains('updated = CURRENT_TIMESTAMP'),
                    self::stringContains('status = ? AND id IN (?)')
                ),
                [BrowserPushNotification::STATUS_PROCESSING, [7, 11]],
                [ParameterType::STRING, ArrayParameterType::INTEGER]
            )
            ->willReturn(2)
        ;
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $repository = new BrowserPushNotificationRepository(
            $entityManager,
            new ClassMetadata(BrowserPushNotification::class)
        );

        self::assertSame(2, $repository->renewProcessingLease([7, 11]));
    }

    public function testUpdatesDenormalizedSenderUsername(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection
            ->expects($this->once())
            ->method('executeStatement')
            ->with(
                'UPDATE browser_push_notification SET sender_username = ? WHERE sender_username = ?',
                ['new-name', 'old-name'],
                [ParameterType::STRING, ParameterType::STRING]
            )
            ->willReturn(3)
        ;
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getConnection')->willReturn($connection);
        $repository = new BrowserPushNotificationRepository(
            $entityManager,
            new ClassMetadata(BrowserPushNotification::class)
        );

        self::assertSame(3, $repository->updateSenderUsername('old-name', 'new-name'));
    }
}
