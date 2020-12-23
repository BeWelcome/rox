<?php

namespace App\Repository;

use App\Entity\Member;

class NotificationRepository extends PaginationRepository
{
    /**
     * @return int
     */
    public function getUncheckedNotificationsCount(Member $member)
    {
        $q = $this->createQueryBuilder('n')
            ->select('count(n.id)')
            ->where('n.member = :member')
            ->setParameter('member', $member)
            ->andWhere('n.checked = 0')
            ->getQuery();

        $unreadCount = $q->getSingleScalarResult();

        return (int) $unreadCount;
    }
}
