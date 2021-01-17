<?php

namespace App\Model;

use App\Entity\Member;
use App\Entity\Trip;
use App\Repository\TripRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

class TripModel
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

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

    public function findInMemberVicinityNextThreeMonths(Member $member, $count = 5)
    {
        // \todo: Get distance from preference
        $distance = 25;

        /** @var TripRepository $tripRepository */
        $tripRepository = $this->entityManager->getRepository(Trip::class);

        // Set to abritrary 3 months
        $duration = 3;
        $trips = $tripRepository->findInVicinityOfMemberNextMonths($member, $count, $duration, $distance);

        return $trips;
    }
}
