<?php

namespace App\Repository;

use App\Entity\NewMember as Member;
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
            ->getQuery()
            ->getResult()
        ;
    }

    public function getLatestImagesFor(Member $member, int $count = 10): array
    {
        return $this->getImagesByMemberQueryBuilder($member)
            ->orderBy('i.created', 'DESC')
            ->setMaxResults($count)
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
