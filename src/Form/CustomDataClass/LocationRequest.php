<?php

namespace App\Form\CustomDataClass;

use App\Entity\Location;

class LocationRequest
{
    public $name;
    public $geoname_id;
    public $latitude;
    public $longitude;

    public function __construct(Location $location = null)
    {
        if (null !== $location) {
            $this->name = $location->getName();
//        if (null !== $location->getAdmin1()) {
//            $this->location .= ', ' . $location->getAdmin1()->getName();
//        }
            $this->name .= ', ' . $location->getCountry()->getName();
            $this->geoname_id = $location->getGeonameId();
            $this->latitude = $location->getLatitude();
            $this->longitude = $location->getLongitude();
        }
    }
}
