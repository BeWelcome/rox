<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Preference;
use App\Entity\Trip;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use function usort;

class TripModel
{
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
        $paginator->setMaxPerPage(20);
        $paginator->setCurrentPage($page);

        return $paginator;
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

    public function validateTrip(Trip $data): bool
    {
        return true;
    }

    public function checkTripCreateOrEditData(Trip $data): array
    {
        $errors = [];
        $legs = $data->getSubtrips();

        $countOptions = 0;
        for ($i = 0; $i < \count($legs); ++$i) {
            for ($j = $i + 1; $j < \count($legs); ++$j) {
                $a = $legs[$i];
                $b = $legs[$j];
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

            if (!empty($legs[$i]->getOptions())) {
                ++$countOptions;
            }
        }

        if (0 === $countOptions) {
            $errors[] = [
                'error' => 'trip.error.no.options',
            ];
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
}
