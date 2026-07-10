<?php

namespace App\Model\MemberDataExtractor;

use App\Entity\BrowserPushNotificationDelivery;
use App\Entity\Member;
use App\Repository\BrowserPushNotificationDeliveryRepository;

final class BrowserPushNotificationDeliveriesExtractor extends AbstractExtractor implements ExtractorInterface
{
    public function extract(Member $member, string $tempDir): string
    {
        /** @var BrowserPushNotificationDeliveryRepository $repository */
        $repository = $this->getRepository(BrowserPushNotificationDelivery::class);
        $deliveries = $repository->createQueryBuilder('delivery')
            ->join('delivery.notification', 'notification')
            ->where('notification.receiver = :member')
            ->setParameter('member', $member)
            ->orderBy('delivery.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;

        return $this->writePersonalDataFile(
            ['browser_push_notification_deliveries' => $deliveries, 'member' => $member],
            'browser_push_notification_deliveries',
            $tempDir . 'browser_push_notification_deliveries.html'
        );
    }
}
