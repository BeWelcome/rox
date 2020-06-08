<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;

class ActivityAttendeeRepository extends EntityRepository
{
    /**
     * Get all activities for a member.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    public function findActivitiesOfMember(Member $member)
    {
        $qb = $this->createQueryBuilder('aa');
        $qb
            ->where('aa.attendee = :member')
            ->setParameter(':member', $member);

        return $qb
            ->getQuery()
            ->getResult();
    }
}
