<?php

/**
 * Created by PhpStorm.
 * User: saeed
 * Date: 12/21/16
 * Time: 11:30 PM.
 */

namespace App\Model;

use AnthonyMartin\GeoLocation\GeoLocation;
use App\Entity\Location;
use Doctrine\ORM\EntityManagerInterface;

class LocationModel
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function getLocationIdsAroundLocation($latitude, $longitude, $distance = 25)
    {
        $coordinates = GeoLocation::fromDegrees($latitude, $longitude)->boundingCoordinates($distance, 'km');

        return $this
            ->entityManager
            ->getRepository(Location::class)
            ->createQueryBuilder('l')
            ->where('l.latitude < ' . $coordinates[1]->getLatitudeInDegrees())
            ->andWhere('l.latitude > ' . $coordinates[0]->getLatitudeInDegrees())
            ->andWhere('l.longitude < ' . $coordinates[1]->getLongitudeInDegrees())
            ->andWhere('l.longitude < ' . $coordinates[0]->getLongitudeInDegrees())
            ->getQuery()
            ->getResult();
    }
}
