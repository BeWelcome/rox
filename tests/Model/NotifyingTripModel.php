<?php

namespace App\Tests\Model;

use App\Entity\Trip;
use App\Model\TripModel;
use Doctrine\ORM\EntityManagerInterface;

class NotifyingTripModel extends TripModel
{
    public array $notifiedTrips = [];

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager);
    }

    public function notifyHostsAboutTrip(Trip $trip): void
    {
        $this->notifiedTrips[] = $trip;
    }
}
