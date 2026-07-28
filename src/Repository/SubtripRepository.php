<?php

namespace App\Repository;

use AnthonyMartin\GeoLocation\GeoPoint;
use App\Doctrine\LegOptionsType;
use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Entity\MemberLegHidden;
use App\Entity\Preference;
use App\Entity\Subtrip;
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

    public function getMembersToNotifyAboutLeg(
        Subtrip $leg,
        array $notificationValues = [
            Preference::TRIP_NOTIFICATIONS_IMMEDIATELY,
        ],
        int $duration = 3,
    ): array {
        $trip = $leg->getTrip();
        $preferenceRepository = $this->getEntityManager()->getRepository(Preference::class);
        $tripNotificationPreference = $preferenceRepository->findOneBy([
            'codename' => Preference::TRIP_NOTIFICATIONS,
        ]);
        $radiusPreference = $preferenceRepository->findOneBy([
            'codename' => Preference::TRIPS_VICINITY_RADIUS,
        ]);

        if (null === $trip || null === $tripNotificationPreference || null === $radiusPreference) {
            return [];
        }

        if (null !== $trip->getDeleted()) {
            return [];
        }

        $activeStatuses = [MemberStatusType::ACTIVE, MemberStatusType::OUT_OF_REMIND];
        if (!\in_array($trip->getCreator()->getStatus(), $activeStatuses, true)) {
            return [];
        }

        $now = CarbonImmutable::today();
        $durationMonthsAhead = $now->addMonths($duration);
        $arrival = $leg->getArrival();
        $location = $leg->getLocation();
        $options = $leg->getOptions();

        if (
            null === $arrival
            || null === $location
            || $arrival < $now
            || $arrival > $durationMonthsAhead
            || \in_array(LegOptionsType::PRIVATE, $options, true)
        ) {
            return [];
        }

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('DISTINCT host')
            ->from(Member::class, 'host')
            ->join('host.addresses', 'a', Join::WITH, 'a.active = true')
            ->join('a.location', 'hostLocation')
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
            ->where($qb->expr()->in('host.status', ':activeStatuses'))
            ->andWhere('host <> :creator')
            ->andWhere('host.maxGuests >= :travellers')
            ->andWhere('COALESCE(tripNotifications.value, :defaultNotification) IN (:tripNotificationValues)')
            ->andWhere(
                'ST_Distance_Sphere(hostLocation.coordinates, ST_GeomFromText(:centerPoint, 0)) <= 1000 * COALESCE(radiusPreference.value, :defaultRadius)'
            )
            ->setParameter('activeStatuses', $activeStatuses)
            ->setParameter('creator', $trip->getCreator())
            ->setParameter('travellers', $trip->getCountOfTravellers())
            ->setParameter('tripNotificationPreference', $tripNotificationPreference)
            ->setParameter('radiusPreference', $radiusPreference)
            ->setParameter('defaultNotification', Preference::TRIP_NOTIFICATIONS_NEVER)
            ->setParameter(
                'tripNotificationValues',
                $notificationValues
            )
            ->setParameter('defaultRadius', $radiusPreference->getDefaultValue())
            ->setParameter('centerPoint', \sprintf('POINT(%F %F)', $location->getLongitude(), $location->getLatitude()))
        ;

        if (null !== $leg->getInvitedBy() && !\in_array(LegOptionsType::MEET_LOCALS, $options, true)) {
            $qb
                ->andWhere('host = :invitedBy')
                ->setParameter('invitedBy', $leg->getInvitedBy())
            ;
        }

        if (0 === $trip->getInvitationRadius()) {
            $qb
                ->andWhere('a.location = :location')
                ->setParameter('location', $location)
            ;
        } else {
            $center = new GeoPoint((float) $location->getLatitude(), (float) $location->getLongitude());
            $boundingBox = $center->boundingBox($trip->getInvitationRadius(), 'km');
            $lineString = \sprintf(
                'LINESTRING(%F %F, %F %F)',
                $boundingBox->getMinLongitude(),
                $boundingBox->getMinLatitude(),
                $boundingBox->getMaxLongitude(),
                $boundingBox->getMaxLatitude()
            );

            $qb
                ->andWhere('hostLocation.latitude BETWEEN :minLat AND :maxLat')
                ->andWhere('hostLocation.longitude BETWEEN :minLng AND :maxLng')
                ->andWhere('MBRContains(ST_Envelope(ST_GeomFromText(:lineString, 0)), hostLocation.coordinates) = 1')
                ->andWhere('ST_Distance_Sphere(hostLocation.coordinates, ST_GeomFromText(:centerPoint, 0)) <= :invitationRadiusMeters')
                ->setParameter('minLat', $boundingBox->getMinLatitude())
                ->setParameter('maxLat', $boundingBox->getMaxLatitude())
                ->setParameter('minLng', $boundingBox->getMinLongitude())
                ->setParameter('maxLng', $boundingBox->getMaxLongitude())
                ->setParameter('lineString', $lineString)
                ->setParameter('invitationRadiusMeters', $trip->getInvitationRadius() * 1000)
            ;
        }

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
            ->leftJoin(MemberLegHidden::class, 'hiddenLeg', Join::WITH, 'hiddenLeg.leg = s AND hiddenLeg.member = :member')
            ->where($qb->expr()->notLike('s.options', $qb->expr()->literal('%' . LegOptionsType::PRIVATE . '%')))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('s.invitedBy'),
                    $qb->expr()->eq('s.invitedBy', $member->getId()),
                    $qb->expr()->in('s.options', [LegOptionsType::MEET_LOCALS])
                )
            )
            ->andWhere('s.arrival >= :now')
            ->andWhere('s.arrival <= :durationMonthsAhead')
            ->andWhere($qb->expr()->in('m.status', ['Active', 'OutOfRemind']))
            ->andWhere('t.creator <> :member')
            ->andWhere($qb->expr()->isNull('t.deleted'))
            ->andWhere($qb->expr()->isNull('hiddenLeg.id'))
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
