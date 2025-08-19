<?php

namespace App\Repository;

use App\Doctrine\CommentAdminActionType;
use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

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

    private function getImagesByMemberQueryBuilder(Member $member): QueryBuilder
    {
        $qb = $this->createQueryBuilder('i');
        $qb
            ->where('i.owner = :member')
            ->setParameter('member', $member)
        ;

        return $qb;
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
}
