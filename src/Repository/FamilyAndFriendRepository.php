<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;

/**
 * FamilyAndFriendRepository.
 */
class FamilyAndFriendRepository extends EntityRepository
{
    public function findRelationsFor(Member $member)
    {
        return
            $this
                ->createQueryBuilder('r')
                ->where('r.owner = :member')
                ->orWhere('r.relation = :member')
                ->setParameter(':member', $member)
                ->orderBy('r.updated', 'ASC')
                ->getQuery()
                ->getResult()
            ;
    }

    public function findRelationBetween(Member $owner, Member $relation)
    {
        return
            $this
                ->createQueryBuilder('r')
                ->where('r.owner = :member')
                ->andWhere('r.relation = :relation')
                ->setParameter(':member', $owner)
                ->setParameter(':relation', $relation)
                ->getQuery()
                ->getResult()
           ;
    }
}
