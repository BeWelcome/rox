<?php

namespace App\Repository;

use AnthonyMartin\GeoLocation\GeoPoint;
use App\Entity\Location;
use Doctrine\ORM\EntityRepository;

class LocationRepository extends EntityRepository
{

    public function getLocationsInVicinity(Location $location, int $distance)
    {
    }
}
