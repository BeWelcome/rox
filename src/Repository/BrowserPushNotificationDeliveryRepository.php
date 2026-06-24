<?php

namespace App\Repository;

use App\Entity\BrowserPushNotification;
use App\Entity\BrowserPushNotificationDelivery;
use Doctrine\ORM\EntityRepository;

class BrowserPushNotificationDeliveryRepository extends EntityRepository
{
    /**
     * @return BrowserPushNotificationDelivery[]
     */
    public function findForNotification(BrowserPushNotification $notification): array
    {
        return $this->createQueryBuilder('d')
            ->where('d.notification = :notification')
            ->setParameter('notification', $notification)
            ->orderBy('d.id', 'asc')
            ->getQuery()
            ->getResult()
        ;
    }
}
