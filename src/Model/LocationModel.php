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
            ->where('MBRContains(ST_Envelope(ST_GeomFromText(:boundingBox, 0)), l.coordinates) = 1')
            ->setParameter('boundingBox', \sprintf(
                'LINESTRING(%F %F, %F %F)',
                $coordinates[0]->getLongitudeInDegrees(),
                $coordinates[0]->getLatitudeInDegrees(),
                $coordinates[1]->getLongitudeInDegrees(),
                $coordinates[1]->getLatitudeInDegrees()
            ))
            ->getQuery()
            ->getResult();
    }
}
