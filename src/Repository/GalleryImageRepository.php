<?php

namespace App\Repository;

use App\Entity\Member;
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

    public function getImagesForMember(Member $member): array
    {
        return $this->getImagesByMemberQueryBuilder($member)
            ->orderBy('i.created', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getLatestImagesFor(Member $member): array
    {
        return $this->getImagesByMemberQueryBuilder($member)
            ->orderBy('i.created', 'DESC')
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
