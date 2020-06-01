<?php

namespace App\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;

/**
 * Class PostNotificationRepository.
 */
class PostNotificationRepository extends EntityRepository
{
    public function getScheduledNotifications($batchSize)
    {
        $date = new DateTime();
        $date->modify('-5 minutes');

        return $this->createQueryBuilder('n')
            ->where('n.status = :toSend')
            ->setParameter(':toSend', 'ToSend')
            ->andWhere('n.created < :date')
            ->setParameter(':date', $date)
            ->orderBy('n.created', 'asc')
            ->setMaxResults($batchSize)
            ->getQuery()
            ->getResult();
    }
}
