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
use App\Utilities\ManagerTrait;

class LocationModel
{
    use ManagerTrait;

    public function getLocationIdsAroundLocation($latitude, $longitude, $distance = 25)
    {
        $coordinates = GeoLocation::fromDegrees($latitude, $longitude)->boundingCoordinates($distance, 'km');

        return $this
            ->em
            ->getRepository(Location::class)
            ->createQueryBuilder('l')
            ->where('l.latitude < '.$coordinates[1]->getLatitudeInDegrees())
            ->where('l.latitude > '.$coordinates[0]->getLatitudeInDegrees())
            ->where('l.longitude < '.$coordinates[1]->getLongitudeInDegrees())
            ->where('l.longitude < '.$coordinates[0]->getLongitudeInDegrees())
            ->getQuery()
            ->getResult();
    }
}
