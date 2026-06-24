<?php

namespace App\Service;

use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushSubscription;
use App\Entity\Member;
use App\Repository\BrowserPushNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BrowserPushSubscriptionRemover
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function removeAllForMember(Member $member): int
    {
        $subscriptions = $this->entityManager->getRepository(BrowserPushSubscription::class)->findBy([
            'member' => $member,
        ]);
        foreach ($subscriptions as $subscription) {
            $this->entityManager->remove($subscription);
        }

        /** @var BrowserPushNotificationRepository $notificationQueue */
        $notificationQueue = $this->entityManager->getRepository(BrowserPushNotification::class);
        $notificationQueue->deletePendingNotificationsForMember($member);

        return \count($subscriptions);
    }
}
