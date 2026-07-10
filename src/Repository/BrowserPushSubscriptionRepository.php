<?php

namespace App\Repository;

use DateTimeInterface;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Doctrine\ORM\EntityRepository;

class BrowserPushSubscriptionRepository extends EntityRepository
{
    public function deleteInactiveSubscriptionsOlderThan(DateTimeInterface $olderThan): int
    {
        try {
            return $this->createQueryBuilder('subscription')
                ->delete()
                ->where('subscription.lastSeen IS NULL OR subscription.lastSeen < :olderThan')
                ->setParameter('olderThan', $olderThan)
                ->getQuery()
                ->execute()
            ;
        } catch (TableNotFoundException) {
            return 0;
        }
    }
}
