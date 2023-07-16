<?php

namespace App\Repository;

use App\Entity\Member;
use App\Entity\Relation;
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
                        $qb->expr()->eq('r.receiver', ':member'),
                    )
            )
            ->setParameter(':member', $member)
            ->setParameter(':confirmed', 'Yes')
            ->orderBy('r.updated', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findRelationBetween(Member $owner, Member $receiver): ?Relation
    {
        return
            $this
                ->createQueryBuilder('r')
                ->where('r.owner = :member')
                ->andWhere('r.receiver = :receiver')
                ->setParameter(':member', $owner)
                ->setParameter(':receiver', $receiver)
                ->getQuery()
                ->getOneOrNullResult()
            ;
    }

    public function findUnconfirmedRelationBetween(Member $owner, Member $receiver): ?Relation
    {
        return
            $this
                ->createQueryBuilder('r')
                ->where('r.owner = :member')
                ->andWhere('r.receiver = :receiver')
                ->andWhere('r.confirmed = :confirmed')
                ->setParameter(':member', $owner)
                ->setParameter(':receiver', $receiver)
                ->setParameter(':confirmed', 'No')
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
            ->setParameter(':confirmed', 'Yes')
            ->orderBy('m.username', 'ASC')
        ;

        $notes = new Pagerfanta(new QueryAdapter($qb->getQuery()));
        $notes->setMaxPerPage($itemsPerPage);
        $notes->setCurrentPage($page);

        return $notes;
    }
}
