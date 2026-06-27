<?php

namespace App\Repository;

use App\Doctrine\MemberStatusType;
use App\Doctrine\SubtripOptionsType;
use App\Entity\Member;
use App\Entity\MemberTripRead;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Entity\Trip;
use Carbon\CarbonImmutable;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
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
            ->setParameter('maxguest', $member->getMaxGuests())
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
            ->setParameter('maxguest', $member->getMaxGuests())
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

    public function getMembersToNotifyAboutTrip(Trip $trip, int $duration = 3): array
    {
        $preferenceRepository = $this->getEntityManager()->getRepository(Preference::class);
        $tripNotificationPreference = $preferenceRepository->findOneBy([
            'codename' => Preference::TRIP_NOTIFICATIONS,
        ]);
        $radiusPreference = $preferenceRepository->findOneBy([
            'codename' => Preference::TRIPS_VICINITY_RADIUS,
        ]);

        if (null === $tripNotificationPreference || null === $radiusPreference) {
            return [];
        }

        $now = CarbonImmutable::today();
        $durationMonthsAhead = $now->addMonths($duration);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('DISTINCT host')
            ->from(Member::class, 'host')
            ->from(Subtrip::class, 's')
            ->join('host.addresses', 'a', Join::WITH, 'a.active = true')
            ->leftJoin(
                'host.preferences',
                'tripNotifications',
                Join::WITH,
                'tripNotifications.preference = :tripNotificationPreference'
            )
            ->leftJoin(
                'host.preferences',
                'radiusPreference',
                Join::WITH,
                'radiusPreference.preference = :radiusPreference'
            )
            ->join('s.location', 'l')
            ->join('s.trip', 't')
            ->join('t.creator', 'creator')
            ->where('s.trip = :trip')
            ->andWhere('s.arrival >= :now')
            ->andWhere('s.arrival <= :durationMonthsAhead')
            ->andWhere($qb->expr()->notLike('s.options', ':privateOption'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('s.invitedBy'),
                    $qb->expr()->eq('s.invitedBy', 'host'),
                    $qb->expr()->in('s.options', ':meetLocalsOptions')
                )
            )
            ->andWhere($qb->expr()->in('host.status', ':activeStatuses'))
            ->andWhere($qb->expr()->in('creator.status', ':activeStatuses'))
            ->andWhere('host <> creator')
            ->andWhere('t.countOfTravellers <= host.maxGuests')
            ->andWhere($qb->expr()->isNull('t.deleted'))
            ->andWhere(
                'COALESCE(tripNotifications.value, :defaultNotification) = :immediately'
            )
            ->andWhere(
                'GeoDistance(a.latitude, a.longitude, l.latitude, l.longitude) <= COALESCE(radiusPreference.value, :defaultRadius)'
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->lte('GeoDistance(a.latitude, a.longitude, l.latitude, l.longitude)', 't.invitationRadius'),
                    $qb->expr()->eq('s.location', 'a.location')
                )
            )
            ->setParameter('trip', $trip)
            ->setParameter('now', $now)
            ->setParameter('durationMonthsAhead', $durationMonthsAhead)
            ->setParameter('privateOption', '%' . SubtripOptionsType::PRIVATE . '%')
            ->setParameter('meetLocalsOptions', [SubtripOptionsType::MEET_LOCALS])
            ->setParameter('activeStatuses', [MemberStatusType::ACTIVE, MemberStatusType::OUT_OF_REMIND])
            ->setParameter('tripNotificationPreference', $tripNotificationPreference)
            ->setParameter('radiusPreference', $radiusPreference)
            ->setParameter('defaultNotification', Preference::TRIP_NOTIFICATIONS_NEVER)
            ->setParameter('immediately', Preference::TRIP_NOTIFICATIONS_IMMEDIATELY)
            ->setParameter('defaultRadius', $radiusPreference->getDefaultValue())
        ;

        return $qb->getQuery()->getResult();
    }

    private function getLegsInAreaQueryBuilder(Member $member, int $distance, int $duration): QueryBuilder
    {
        $address = $member->getActiveAddress();
        $location = false === $address ? null : $address->getLocation();
        $latitude = false === $address ? null : $address->getLatitude();
        $longitude = false === $address ? null : $address->getLongitude();

        $now = CarbonImmutable::today();
        $durationMonthsAhead = $now->addMonths($duration);

        $qb = $this->createQueryBuilder('s');
        $qb
            ->join('s.location', 'l')
            ->join('s.trip', 't')
            ->join('t.creator', 'm')
            ->leftJoin(MemberTripRead::class, 'readTrip', Join::WITH, 'readTrip.trip = t AND readTrip.member = :member')
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
            ->andWhere($qb->expr()->isNull('readTrip.id'))
            ->andWhere('GeoDistance(:latitude, :longitude, l.latitude, l.longitude) <= :distance')
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->lte('GeoDistance(:latitude, :longitude, l.latitude, l.longitude)', 't.invitationRadius'),
                    $qb->expr()->eq('s.location', ':location')
                )
            )
            ->setParameter('distance', $distance)
            ->setParameter('member', $member)
            ->setParameter('location', $location)
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('now', $now)
            ->setParameter('durationMonthsAhead', $durationMonthsAhead)
            ->orderBy('s.arrival', 'ASC')
            ->addSelect('t')
        ;

        return $qb;
    }
}
