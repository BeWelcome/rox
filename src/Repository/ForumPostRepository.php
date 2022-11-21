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

    public function getForumPostsByMember(Member $member, string $search, int $page, int $itemsPerPage = 20): PagerFanta
    {
        $queryBuilder = $this->getForumPostsByMemberQueryBuilder($member);
        if (!empty($search)) {
            $parts = explode(' ', $search);
            foreach ($parts as $part) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->like('fp.message', $queryBuilder->expr()->literal('%' . $part . '%'))
                );
            }
        }
        $queryBuilder->orderBy('fp.created', 'DESC');

        $adapter = new QueryAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta
            ->setMaxPerPage($itemsPerPage)
            ->setCurrentPage($page)
        ;

        return $pagerfanta;
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
