<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\MemberSubtripHidden;
use App\Entity\MemberTripNotificationSent;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Repository\SubtripRepository;
use App\Repository\TripRepository;
use App\Service\Mailer;
use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class TripModel
{
    private const array ALLOWED_TRIPS_RADIUS = [0, 5, 10, 20, 50, 100];
    private const array SCHEDULED_TRIP_NOTIFICATIONS = [
        Preference::TRIP_NOTIFICATIONS_DAILY,
        Preference::TRIP_NOTIFICATIONS_WEEKLY,
        Preference::TRIP_NOTIFICATIONS_MONTHLY,
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ?Mailer $mailer = null,
    ) {
    }

    public function paginateTripsOfMember(Member $member, int $page): Pagerfanta
    {
        /** @var TripRepository $repository */
        $repository = $this->entityManager->getRepository(Trip::class);
        $query = $repository->queryTripsOfMember($member);

        $paginator = new Pagerfanta(new QueryAdapter($query, false));
        // \todo: Remove after testing.
        $paginator->setMaxPerPage(10);
        $paginator->setCurrentPage($page);

        return $paginator;
    }

    public function checkTripsRadius($member, $radius)
    {
        if (!\in_array($radius, self::ALLOWED_TRIPS_RADIUS, true)) {
            return $this->getTripsRadius($member);
        }

        return $radius;
    }

    public function setTripsRadius($member, $radius): void
    {
        $preferenceRepository = $this->entityManager->getRepository(Preference::class);

        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::TRIPS_VICINITY_RADIUS]);
        $memberPreference = $member->getMemberPreference($preference);
        $memberPreference->setValue($radius);
        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();
    }

    public function getTripsRadius(Member $member): int
    {
        $preferenceRepository = $this->entityManager->getRepository(Preference::class);

        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy(['codename' => Preference::TRIPS_VICINITY_RADIUS]);
        $memberPreference = $member->getMemberPreference($preference);

        return (int) $memberPreference->getValue();
    }

    public function checkTripCreateOrEditData(Trip $data): array
    {
        $errors = [];
        $legs = $data->getSubtrips();
        $keys = $legs->getKeys();

        for ($i = 0; $i < \count($keys); ++$i) {
            for ($j = $i + 1; $j < \count($keys); ++$j) {
                $a = $legs[$keys[$i]];
                $b = $legs[$keys[$j]];
                // (StartA < EndB) and (EndA > StartB)
                if ($a->getArrival() < $b->getDeparture() && $a->getDeparture() > $b->getArrival()) {
                    $errors[] = [
                        'leg' => $i,
                        'field' => 'duration',
                        'error' => 'trip.error.date.overlap',
                    ];
                    $errors[] = [
                        'leg' => $j,
                        'field' => 'duration',
                        'error' => 'trip.error.date.overlap',
                    ];
                }
            }

            if (empty($legs[$keys[$i]]->getOptions())) {
                $errors[] = [
                    'leg' => $i,
                    'field' => 'options',
                    'error' => 'trip.error.no.options',
                ];
            }
        }

        return $errors;
    }

    public function orderTripLegs(Trip &$trip): void
    {
        $legs = iterator_to_array($trip->getSubtrips());
        usort($legs, static function ($a, $b) {
            $arrivalA = $a->getArrival();
            $arrivalB = $b->getArrival();

            if ($arrivalA === $arrivalB) {
                return 0;
            }

            return ($arrivalA <= $arrivalB) ? -1 : 1;
        });

        foreach ($trip->getSubtrips() as $leg) {
            $trip->removeSubtrip($leg);
        }

        foreach ($legs as $leg) {
            $trip->addSubtrip($leg);
        }
    }

    public function hideTrip(Trip $trip): void
    {
        $trip->setDeleted(new DateTime());

        $this->entityManager->persist($trip);
        $this->entityManager->flush();
    }

    public function markSubtripAsHidden(Member $member, Subtrip $subtrip): void
    {
        $repository = $this->entityManager->getRepository(MemberSubtripHidden::class);
        $hidden = $repository->findOneBy(['member' => $member, 'subtrip' => $subtrip]);

        if (null === $hidden) {
            $this->entityManager->persist(new MemberSubtripHidden($member, $subtrip));
            $this->entityManager->flush();
        }
    }

    public function notifyHostsAboutTrip(Trip $trip): void
    {
        $this->sendTripNotifications($trip, [Preference::TRIP_NOTIFICATIONS_IMMEDIATELY]);
    }

    public function sendScheduledTripNotifications(
        string $frequency,
        DateTimeInterface $since,
        DateTimeInterface $until,
    ): int {
        if (!\in_array($frequency, self::SCHEDULED_TRIP_NOTIFICATIONS, true)) {
            throw new InvalidArgumentException('Unsupported trip notification frequency.');
        }

        /** @var TripRepository $tripRepository */
        $tripRepository = $this->entityManager->getRepository(Trip::class);
        $trips = $tripRepository->findTripsCreatedBetween($since, $until);

        $sent = 0;
        foreach ($trips as $trip) {
            $sent += $this->sendTripNotifications($trip, [$frequency]);
        }

        return $sent;
    }

    public function hasTripExpired(Trip $trip)
    {
        return $trip->isExpired();
    }

    public function copyTrip(Trip $trip)
    {
        $em = $this->entityManager;

        $newTrip = clone $trip;
        $newTrip->setSummary($trip->getSummary() . ' - copy');
        $newTrip->setUpdated(new DateTime());

        // Move legs arrival and departure consistently +1month
        $nextMonth = new DateTime()->modify('+1month');
        $firstArrival = $trip->getSubtrips()->first()->getArrival();
        $adjust = $firstArrival->diff($nextMonth);

        foreach ($trip->getSubTrips() as $leg) {
            $newLeg = clone $leg;
            $newLeg->setArrival($leg->getArrival()->add($adjust));
            $newLeg->setDeparture($leg->getDeparture()->add($adjust));
            $newLeg->setInvitedBy(null);
            $newTrip->addSubTrip($newLeg);
            $em->persist($newLeg);
            $em->flush();
        }

        $em->persist($newTrip);
        $em->flush();

        return $newTrip;
    }

    private function sendTripNotifications(Trip $trip, array $notificationValues): int
    {
        /** @var SubtripRepository $repository */
        $repository = $this->entityManager->getRepository(Subtrip::class);
        $hosts = $repository->getMembersToNotifyAboutTrip($trip, $notificationValues);

        if ([] === $hosts) {
            return 0;
        }

        $sentRepository = $this->entityManager->getRepository(MemberTripNotificationSent::class);
        $sent = [];
        $sentCount = 0;
        foreach ($hosts as $host) {
            $hostId = $host->getId();
            if (isset($sent[$hostId])) {
                continue;
            }

            $sent[$hostId] = true;
            $lockName = $this->acquireTripNotificationLock($host, $trip);
            if (null === $lockName) {
                continue;
            }

            try {
                if (null !== $sentRepository->findOneBy(['member' => $host, 'trip' => $trip])) {
                    continue;
                }

                if (true !== $this->mailer?->sendTripNotificationEmail($host, $trip)) {
                    continue;
                }

                $this->entityManager->persist(new MemberTripNotificationSent($host, $trip));
                $this->entityManager->flush();
                ++$sentCount;
            } finally {
                $this->releaseTripNotificationLock($lockName);
            }
        }

        return $sentCount;
    }

    private function acquireTripNotificationLock(Member $member, Trip $trip): ?string
    {
        $lockName = \sprintf('trip-notification:%d:%d', $member->getId(), $trip->getId());
        $locked = $this->entityManager->getConnection()->fetchOne('SELECT GET_LOCK(?, 0)', [$lockName]);

        return 1 === (int) $locked ? $lockName : null;
    }

    private function releaseTripNotificationLock(string $lockName): void
    {
        $this->entityManager->getConnection()->fetchOne('SELECT RELEASE_LOCK(?)', [$lockName]);
    }
}
