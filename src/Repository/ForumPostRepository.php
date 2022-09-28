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

class ForumPostRepository extends EntityRepository
{
    public function getForumPostsByMemberCount(Member $member): int
    {
        return $this->getForumPostsByMemberQueryBuilder($member)
            ->select('count(fp.id)')
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }

    public function getForumPostsByMember(Member $member): Collection
    {
        return $this->getForumPostsByMemberQueryBuilder($member)
            ->getQuery()
            ->getResult()
            ;
    }

    private function getForumPostsByMemberQueryBuilder(Member $member): QueryBuilder
    {
        $qb = $this->createQueryBuilder('fp');
        $qb
            ->where('fp.author = :member')
            ->setParameter('member', $member)
        ;

        return $qb;
    }
}
