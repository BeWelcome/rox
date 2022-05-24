<?php

namespace App\Form\CustomDataClass;

use App\Entity\NewLocation;

class LocationRequest
{
    public string $name;
    public int $geonameId;
    public float $latitude;
    public float $longitude;

    public function __construct(NewLocation $location = null)
    {
        if (null !== $location) {
            $this->name = $location->getFullName();
            $this->geonameId = $location->getGeonameId();
            $this->latitude = $location->getLatitude();
            $this->longitude = $location->getLongitude();
        }
    }
}
