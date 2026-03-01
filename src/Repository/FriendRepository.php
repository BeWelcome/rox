<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Entity\Friend;
use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

class FriendRepository extends EntityRepository
{
    public function getFamilyAndFriendsCount(Member $member): int
    {
        $qb = $this->createQueryBuilder('f');

        return
            (int) $qb
                ->select('count(f.left)')
                ->join('f.left', 'l')
                ->join('f.right', 'r')
                ->where(
                    $qb->expr()->andX(
                        $qb->expr()->eq('f.leftConfirmed', true),
                        $qb->expr()->eq('f.rightConfirmed', true)
                    )
                )
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('f.left', ':member'),
                        $qb->expr()->eq('f.right', ':member')
                    )
                )
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->in('l.status', ':status'),
                        $qb->expr()->in('r.status', ':status')
                    )
                )
                ->setParameter('member', $member)
                ->setParameter('status', MemberStatusType::ACTIVE_ALL_ARRAY)
                ->getQuery()
                ->getSingleScalarResult()
        ;
    }

    public function getNumberOfFriendsFor(Member $member): int
    {
        $qb = $this->getFriendsForQueryBuilder($member);
        $qb->select('count(f.left)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findFriendsFor(Member $member): mixed
    {
        $qb = $this->createQueryBuilder('f');

        return $qb
            ->join('f.left', 'l')
            ->join('f.right', 'r')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('f.leftConfirmed', true),
                    $qb->expr()->eq('f.rightConfirmed', true)
                )
            )
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
            ->setParameter('status', MemberStatusType::ACTIVE_ALL_ARRAY)
            ->orderBy('f.updated', 'DESC')
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

        $qb = $this->createQueryBuilder('f');

        return
            $qb
                ->where('f.left = :left')
                ->andWhere('f.right = :right')
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('f.leftConfirmed', 0),
                        $qb->expr()->eq('f.rightConfirmed', 0)
                    )
                )
                ->setParameter('left', $left)
                ->setParameter('right', $right)
                ->getQuery()
                ->getOneOrNullResult()
        ;
    }

    public function getFriends(Member $member, int $page, int $itemsPerPage = 50): Pagerfanta
    {
        $qb = $this->getFriendsForQueryBuilder($member);

        $query = $qb->getQuery();
        $results = $query->getResult();

        $friends = new Pagerfanta(new ArrayAdapter($results));

        // $items per page will be 5, 10, 20, 50, 100 here we want a number dividable by 6
        // as for bigger viewports we use 6 friends per row. We use the next larger number
        // that is dividable by 6 (6, 12, 24, 54, 102).
        $itemsPerPage = ($itemsPerPage / 6 + 1) * 6;
        $friends->setMaxPerPage($itemsPerPage);
        $friends->setCurrentPage($page);

        return $friends;
    }

    private function getFriendsForQueryBuilder($member): QueryBuilder
    {
        $qb = $this->createQueryBuilder('f');

        return $qb
            ->join('f.left', 'l')
            ->join('f.right', 'r')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('f.leftConfirmed', ':confirmed'),
                    $qb->expr()->eq('f.rightConfirmed', ':confirmed')
                )
            )
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
            ->setParameter('confirmed', true)
            ->setParameter('status', MemberStatusType::ACTIVE_ALL_ARRAY)
            ->orderBy('f.updated', 'DESC')
        ;
    }
}
