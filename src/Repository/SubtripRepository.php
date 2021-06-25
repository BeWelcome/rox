<?php

namespace App\Repository;

use AnthonyMartin\GeoLocation\GeoPoint;
use App\Doctrine\SubtripOptionsType;
use App\Entity\Member;
use Carbon\CarbonImmutable;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
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
    public function queryTripsOfMember(Member $member): Query
    {
        return $this->createQueryBuilder('t')
            ->where('t.created <= :now')
            ->andWhere('t.creator = :creator')
            ->setParameter(':now', new DateTime())
            ->setParameter(':creator', $member)
            ->orderBy('t.created', 'DESC')
            ->getQuery();
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
        $location = $member->getCity();

        // Fetch latitude and longitude of member's location
        $latitude = $location->getLatitude();
        $longitude = $location->getLongitude();

        $geoPoint = new GeoPoint($latitude, $longitude);
        $boundingBox = $geoPoint->boundingBox($distance, 'km');

        $now = new CarbonImmutable();
        $threeMonths = $now->addMonths($duration);

        $qb = $this->createQueryBuilder('s');
        $qb
            ->join('s.location', 'l')
            ->join('s.trip', 't')
            ->join('t.creator', 'm')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->isNull('s.invitedBy'),
                    $qb->expr()->eq('s.invitedBy', $member->getId())
                )
            )
            ->andWhere($qb->expr()->notIn('s.options', [SubtripOptionsType::PRIVATE]))
            ->andWhere('GeoDistance(m.latitude, m.longitude, l.latitude, l.longitude) < :distance')
            ->andWhere('GeoDistance(m.latitude, m.longitude, l.latitude, l.longitude) < t.invitationRadius')
            ->setParameter(':distance', $distance)
            ->andWhere('s.arrival >= :now')
            ->andWhere('s.arrival <= :threeMonths')
            ->andWhere($qb->expr()->in('m.status', ['Active', 'OutOfRemind']))
            ->andWhere('t.creator <> :member')
            ->andWhere($qb->expr()->isNull('t.deleted'))
            ->setParameter(':member', $member)
            ->setParameter(':now', $now)
            ->setParameter(':threeMonths', $threeMonths)
            ->addSelect('t');

        return $qb;
    }
}
