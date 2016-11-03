<?php

namespace Rox\Geo\Repository;

use Rox\Core\Exception\NotFoundException;

interface LocationRepositoryInterface
{
    /**
     * @param float $latitude
     * @param float $longitude
     * @param int $distance
     * @return array Rox\Geo\Model\Location
     *
     * @throws NotFoundException
     *
     */
    public function getLocationIdsAroundLocation($latitude, $longitude, $distance = 25);
}
