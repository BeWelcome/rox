<?php

namespace App\Repository;

use App\Entity\Member;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

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
