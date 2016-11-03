<?php

namespace Rox\Geo\Model;

use AnthonyMartin\GeoLocation\GeoLocation;
use Rox\Core\Exception\NotFoundException;
use Rox\Core\Model\AbstractModel;
use Rox\Geo\Repository\LocationRepositoryInterface;

class Location extends AbstractModel implements LocationRepositoryInterface
{
    /**
     * @var string
     */
    public $table = 'geonames';

    /**
     * @var boolean
     */
    public $timestamps = false;

    /**
     * @var string
     */
    protected $primaryKey = 'geonameid';

    /**
     * @var array
     */
    protected $ormRelationships = [
        'country',
    ];

    public function Country()
    {
        return $this->hasOne(Country::class, 'country', 'countryCode');
    }

    public function getCountryCodeAttribute()
    {
        return $this->attributes['country'];
    }

    /**
     * @param float $latitude
     * @param float $longitude
     * @param int $distance
     * @return array Rox\Geo\Model\Location
     *
     * @throws NotFoundException
     *
     */
    public function getLocationIdsAroundLocation($latitude, $longitude, $distance = 25)
    {
        $coordinates = GeoLocation::fromDegrees($latitude, $longitude)->boundingCoordinates($distance, 'km');
        return $this->newQuery()
            ->where('latitude', '<', $coordinates[1]->getLatitudeInDegrees())
            ->where('latitude', '>', $coordinates[0]->getLatitudeInDegrees())
            ->where('longitude', '<', $coordinates[1]->getLongitudeInDegrees())
            ->where('longitude', '>', $coordinates[0]->getLongitudeInDegrees())
            ->get(['geonameId'])->map(
                function ($location) {
                    return $location->geonameId;
                })
            ->all();
    }
}
