<?php

namespace App\Tests\Command;

use App\Command\BrowserPushRetentionCommand;
use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushSubscription;
use App\Repository\BrowserPushNotificationRepository;
use App\Repository\BrowserPushSubscriptionRepository;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class BrowserPushRetentionCommandTest extends TestCase
{
    public function testRemovesExpiredBrowserPushData(): void
    {
        $notificationRepository = $this->createMock(BrowserPushNotificationRepository::class);
        $notificationRepository->expects($this->once())
            ->method('deleteNotificationsOlderThan')
            ->with(self::callback(static fn (DateTimeInterface $date): bool => $date < new DateTimeImmutable('-6 days')))
            ->willReturn(3)
        ;
        $subscriptionRepository = $this->createMock(BrowserPushSubscriptionRepository::class);
        $subscriptionRepository->expects($this->once())
            ->method('deleteInactiveSubscriptionsOlderThan')
            ->with(self::callback(static fn (DateTimeInterface $date): bool => $date < new DateTimeImmutable('-11 months')))
            ->willReturn(2)
        ;
        $entityManager = $this->createStub(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturnMap([
            [BrowserPushNotification::class, $notificationRepository],
            [BrowserPushSubscription::class, $subscriptionRepository],
        ]);

        $tester = new CommandTester(new BrowserPushRetentionCommand($entityManager));

        self::assertSame(0, $tester->execute([]));
        self::assertStringContainsString(
            'Removed 3 browser push notifications and 2 inactive subscriptions.',
            $tester->getDisplay()
        );
    }
}
