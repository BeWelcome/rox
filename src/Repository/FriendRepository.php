<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Friend;
use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * FamilyAndFriendRepository.
 */
class FriendRepository extends EntityRepository
{
    public function getFamilyAndFriendsCount(Member $member): int
    {
        $qb = $this->createQueryBuilder('r');

        return
            (int) $qb
                ->select('count(r.left)')
                ->where('r.confirmed = :confirmed')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('r.left', ':member'),
                        $qb->expr()->eq('r.right', ':member')
                    )
                )
                ->setParameter('member', $member)
                ->setParameter('confirmed', true)
                ->getQuery()
                ->getSingleScalarResult()
        ;
    }

    public function findFriendsFor(Member $member): mixed
    {
        $qb = $this->createQueryBuilder('f');

        return $qb
            ->where('f.confirmed = :confirmed')
            ->join('f.left', 'l')
            ->join('f.right', 'r')
            ->andWhere(
                $qb->expr()
                    ->orX(
                        $qb->expr()->eq('f.left', ':member'),
                        $qb->expr()->eq('f.right', ':member'),
                    )
            )
            ->andWhere($qb->expr()->in('l.status', ':status'))
            ->andWhere($qb->expr()->in('r.status', ':status'))
            ->setParameter('member', $member)
            ->setParameter('confirmed', 1)
            ->setParameter('status', MemberStatusType::MEMBER_COMMENTS_ARRAY)
            ->orderBy('r.updated', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findFriendshipBetween(Member $relation1, Member $relation2): ?Friend
    {
        $left = $relation1->getId() < $relation2->getId() ? $relation1 : $relation2;
        $right = $relation1->getId() < $relation2->getId() ? $relation2 : $relation1;

        return
            $this
                ->createQueryBuilder('r')
                ->where('r.left = :left')
                ->andWhere('r.right = :right')
                ->setParameter('left', $left)
                ->setParameter('right', $right)
                ->getQuery()
                ->getOneOrNullResult()
        ;
    }

    public function findUnconfirmedRelationBetween(Member $relation1, Member $relation2): ?Friend
    {
        $left = $relation1->getId() < $relation2->getId() ? $relation1 : $relation2;
        $right = $relation1->getId() < $relation2->getId() ? $relation2 : $relation1;

        return
            $this
                ->createQueryBuilder('r')
                ->where('r.left = :left')
                ->andWhere('r.right = :right')
                ->setParameter('left', $left)
                ->setParameter('right', $right)
                ->setParameter('confirmed', 'No')
                ->getQuery()
                ->getOneOrNullResult()
        ;
    }

    public function getRelations(Member $member, int $page, int $itemsPerPage): Pagerfanta
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.receiver', 'm')
            ->where('r.confirmed = :confirmed')
            ->andWhere('r.owner = :member')
            ->setParameter('member', $member)
            ->setParameter('confirmed', 'Yes')
            ->orderBy('m.username', 'ASC')
        ;

        $notes = new Pagerfanta(new QueryAdapter($qb->getQuery()));
        $notes->setMaxPerPage($itemsPerPage);
        $notes->setCurrentPage($page);

        return $notes;
    }
}
