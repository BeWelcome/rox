<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Repository\TripRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class TripModel
{
    private const ALLOWED_TRIPS_RADIUS = [0, 5, 10, 20, 50, 100, 200, 500, 1000, 2000, 5000];

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function paginateTripsOfMember(Member $member, int $page): PagerFanta
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

    public function setTripsRadius($member, $radius)
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

        return (int) ($memberPreference->getValue());
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
                // (StartA <= EndB) and (EndA >= StartB)
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
        usort($legs, function ($a, $b) {
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

    public function hideTrip(Trip $trip)
    {
        $trip->setDeleted(new DateTIme());

        $this->entityManager->persist($trip);
        $this->entityManager->flush();
    }

    public function hasTripExpired(Trip $trip)
    {
        $legs = $trip->getSubtrips();

        if (0 === $legs->count()) {
            throw new InvalidArgumentException('No trip legs');
        }

        $expired = true;
        $now = new Carbon();

        /** @var Subtrip $leg */
        foreach ($legs->getIterator() as $leg) {
            $arrival = $leg->getArrival();
            $expired = $expired && ($arrival < $now);
        }

        return $expired;
    }
}
