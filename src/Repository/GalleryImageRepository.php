<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class GalleryImageRepository extends EntityRepository
{
    public function getImagesByMemberCount(Member $member): int
    {
        return $this->getImagesByMemberQueryBuilder($member)
            ->select('count(i.id)')
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    public function getImagesByMember(Member $member): Collection
    {
        return $this->getImagesByMemberQueryBuilder($member)
            ->getQuery()
            ->getResult()
        ;
    }

    private function getImagesByMemberQueryBuilder(Member $member): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');
        $qb
            ->where('i.owner = :member')
            ->setParameter('member', $member)
        ;

        return $qb;
    }
}
