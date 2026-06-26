<?php

namespace App\Tests\Service;

use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushSubscription;
use App\Entity\Member;
use App\Repository\BrowserPushNotificationRepository;
use App\Service\BrowserPushSubscriptionRemover;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;

class BrowserPushSubscriptionRemoverTest extends TestCase
{
    public function testRemoveAllForMemberRemovesSubscriptionsAndPendingNotifications(): void
    {
        $member = new Member();
        $subscription = new BrowserPushSubscription()
            ->setMember($member)
            ->setEndpoint('https://fcm.googleapis.com/fcm/send/test')
            ->setPublicKey('public-key')
            ->setAuthToken('auth-token')
        ;
        $subscriptionRepository = $this->createMock(EntityRepository::class);
        $subscriptionRepository->expects($this->once())
            ->method('findBy')
            ->with(['member' => $member])
            ->willReturn([$subscription])
        ;
        $notificationRepository = $this->createMock(BrowserPushNotificationRepository::class);
        $notificationRepository->expects($this->once())
            ->method('deletePendingNotificationsForMember')
            ->with($member)
            ->willReturn(1)
        ;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturnMap([
            [BrowserPushSubscription::class, $subscriptionRepository],
            [BrowserPushNotification::class, $notificationRepository],
        ]);
        $entityManager->expects($this->once())->method('remove')->with($subscription);

        $remover = new BrowserPushSubscriptionRemover($entityManager);

        self::assertSame(1, $remover->removeAllForMember($member));
    }
}
