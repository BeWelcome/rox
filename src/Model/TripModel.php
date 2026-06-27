<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\MemberTripRead;
use App\Entity\Notification;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Repository\SubtripRepository;
use App\Repository\TripRepository;
use App\Service\Mailer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class TripModel
{
    private const array ALLOWED_TRIPS_RADIUS = [0, 5, 10, 20, 50, 100];

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

    public function markTripAsRead(Member $member, Trip $trip): void
    {
        $repository = $this->entityManager->getRepository(MemberTripRead::class);
        $read = $repository->findOneBy(['member' => $member, 'trip' => $trip]);
        $changed = false;

        if (null === $read) {
            $this->entityManager->persist(new MemberTripRead($member, $trip));
            $changed = true;
        }

        $notificationRepository = $this->entityManager->getRepository(Notification::class);
        $notifications = $notificationRepository->findBy([
            'member' => $member,
            'type' => 'trip',
            'link' => '/trip/' . $trip->getId(),
            'checked' => false,
        ]);

        foreach ($notifications as $notification) {
            $notification->setChecked(true);
            $changed = true;
        }

        if ($changed) {
            $this->entityManager->flush();
        }
    }

    public function notifyHostsAboutTrip(Trip $trip): void
    {
        /** @var SubtripRepository $repository */
        $repository = $this->entityManager->getRepository(Subtrip::class);
        $hosts = $repository->getMembersToNotifyAboutTrip($trip);

        if ([] === $hosts) {
            return;
        }

        foreach ($hosts as $host) {
            $notification = new Notification();
            $notification
                ->setMember($host)
                ->setRelMember($trip->getCreator())
                ->setType('trip')
                ->setLink('/trip/' . $trip->getId())
                ->setWordcode('trip.notification.new')
                ->setChecked(false)
                ->setSendmail(null !== $this->mailer)
            ;

            $this->entityManager->persist($notification);
            $this->mailer?->sendTripNotificationEmail($host, $trip);
        }

        $this->entityManager->flush();
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
}
