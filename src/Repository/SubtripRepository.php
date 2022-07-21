<?php

namespace App\Repository;

use App\Doctrine\SubtripOptionsType;
use App\Entity\Member;
use Carbon\CarbonImmutable;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;

/**
 * This custom Doctrine repository contains some methods which are useful when
 * querying for blog post information.
 * See http://symfony.com/doc/current/book/doctrine.html#custom-repository-classes.
 *
 * @author Ryan Weaver <weaverryan@gmail.com>
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 */
class SubtripRepository extends EntityRepository
{
    public function getVisitorsCount(Member $member, int $distance = 20, int $duration = 3): int
    {
        $queryBuilder = $this->getLegsInAreaQueryBuilder($member, $distance, $duration);
        $queryBuilder
            ->select('count(s.id)')
            ->andWhere('t.countOfTravellers <= :maxguest')
            ->setParameter(':maxguest', $member->getMaxguest())
        ;

        return
            $queryBuilder
                ->getQuery()
                ->getSingleScalarResult()
            ;
    }

    public function getLegsInAreaMaxGuests(Member $member, int $distance = 20, int $duration = 3): array
    {
        $queryBuilder = $this->getLegsInAreaQueryBuilder($member, $distance, $duration);
        $queryBuilder
            ->andWhere('t.countOfTravellers <= :maxguest')
            ->setParameter(':maxguest', $member->getMaxguest())
            ->setMaxResults(5)
        ;

        return
            $queryBuilder
                ->getQuery()
                ->getResult()
            ;
    }

    public function getLegsInAreaQuery(Member $member, int $radius = 20, int $duration = 3): Query
    {
        return
            $this
                ->getLegsInAreaQueryBuilder($member, $radius, $duration)
                ->getQuery();
    }

    private function getLegsInAreaQueryBuilder(Member $member, int $distance, int $duration): QueryBuilder
    {
        $now = new CarbonImmutable();
        $durationMonthsAhead = $now->addMonths($duration);

        $qb = $this->createQueryBuilder('s');
        $qb
            ->join('s.location', 'l')
            ->join('s.trip', 't')
            ->join('t.creator', 'm')
            ->where($qb->expr()->notLike('s.options', $qb->expr()->literal('%' . SubtripOptionsType::PRIVATE . '%')))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('s.invitedBy'),
                    $qb->expr()->eq('s.invitedBy', $member->getId()),
                    $qb->expr()->in('s.options', [SubtripOptionsType::MEET_LOCALS])
                )
            )
            ->andWhere('s.arrival >= :now')
            ->andWhere('s.arrival <= :durationMonthsAhead')
            ->andWhere($qb->expr()->in('m.status', ['Active', 'OutOfRemind']))
            ->andWhere('t.creator <> :member')
            ->andWhere($qb->expr()->isNull('t.deleted'))
            ->andWhere('GeoDistance(:latitude, :longitude, l.latitude, l.longitude) <= :distance')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->lte('GeoDistance(:latitude, :longitude, l.latitude, l.longitude)', 't.invitationRadius'),
                    $qb->expr()->eq('s.location', ':city')
                )
            )
            ->setParameter(':distance', $distance)
            ->setParameter(':member', $member)
            ->setParameter(':city', $member->getCity())
            ->setParameter(':latitude', $member->getLatitude())
            ->setParameter(':longitude', $member->getLongitude())
            ->setParameter(':now', $now)
            ->setParameter(':durationMonthsAhead', $durationMonthsAhead)
            ->orderBy('s.arrival', 'ASC')
            ->addSelect('t')
        ;

        return $qb;
    }
}
