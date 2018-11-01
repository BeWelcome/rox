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

class LocationModel extends BaseModel
{
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
        //        return $this->newQuery()
//            ->where('latitude', '<', $coordinates[1]->getLatitudeInDegrees())
//            ->where('latitude', '>', $coordinates[0]->getLatitudeInDegrees())
//            ->where('longitude', '<', $coordinates[1]->getLongitudeInDegrees())
//            ->where('longitude', '>', $coordinates[0]->getLongitudeInDegrees())
//            ->get(['geonameId'])->map(
//                function ($location) {
//                    return $location->geonameId;
//                }
//            )
//            ->all();
    }
}
