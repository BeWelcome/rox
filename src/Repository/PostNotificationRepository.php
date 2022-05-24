<?php

namespace App\Repository;

use App\Doctrine\NotificationStatusType;
use DateTime;
use Doctrine\ORM\EntityRepository;

/**
 * Class PostNotificationRepository.
 */
class PostNotificationRepository extends EntityRepository
{
    /**
     * @return int|mixed|string
     */
    public function getScheduledNotifications(int $batchSize)
    {
        $date = new DateTime();
        $date->modify('-5 minutes');

        return $this->createQueryBuilder('n')
            ->where('n.status = :toSend')
            ->setParameter(':toSend', NotificationStatusType::SCHEDULED)
            ->andWhere('n.created < :date')
            ->setParameter(':date', $date)
            ->orderBy('n.created', 'asc')
            ->setMaxResults($batchSize)
            ->getQuery()
            ->getResult();
    }
}
