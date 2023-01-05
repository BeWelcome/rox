<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

/**
 * FamilyAndFriendRepository.
 */
class RelationRepository extends EntityRepository
{
    public function getRelationsCount(Member $member): int
    {
        $qb = $this->createQueryBuilder('r');

        return
            (int) $qb
                ->select('count(r.id)')
                ->where('r.confirmed = :confirmed')
                ->andWhere(
                    $qb->expr()->eq('r.owner', ':member'),
                )
                ->setParameter(':member', $member)
                ->setParameter(':confirmed', 'Yes')
                ->orderBy('r.updated', 'ASC')
                ->getQuery()
                ->getSingleScalarResult()
            ;
    }

    public function findRelationsFor(Member $member)
    {
        $qb = $this->createQueryBuilder('r');

        return $qb
            ->where('r.confirmed = :confirmed')
            ->andWhere(
                $qb->expr()
                    ->orX(
                        $qb->expr()->eq('r.owner', ':member'),
                        $qb->expr()->eq('r.relation', ':member'),
                    )
            )
            ->setParameter(':member', $member)
            ->setParameter(':confirmed', 'Yes')
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
                ->andWhere('r.confirmed = :confirmed')
                ->setParameter(':member', $owner)
                ->setParameter(':relation', $relation)
                ->setParameter(':confirmed', 'Yes')
                ->getQuery()
                ->getOneOrNullResult()
           ;
    }

    public function getRelations(Member $member, int $page, int $itemsPerPage): Pagerfanta
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.confirmed = :confirmed')
            ->andWhere('r.owner = :member')
            ->setParameter('member', $member)
            ->setParameter(':confirmed', 'Yes')
        ;

        $notes = new Pagerfanta(new QueryAdapter($qb->getQuery()));
        $notes->setMaxPerPage($itemsPerPage);
        $notes->setCurrentPage($page);

        return $notes;
    }
}
