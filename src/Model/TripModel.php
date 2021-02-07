<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Preference;
use App\Entity\Trip;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

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
}
