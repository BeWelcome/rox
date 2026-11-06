<?php

namespace App\Repository;

use AnthonyMartin\GeoLocation\GeoPoint;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\QueryBuilder;

/**
 * The places (location.geoname_id) inside the rectangle around a point, for the member search, the trips and
 * the activities.
 *
 * Plain SQL on purpose: MariaDB only uses the spatial index of location.coordinates for a bare MBRContains().
 * DQL needs a comparison (MBRContains(...) = 1), and with that MariaDB reads every active address instead of
 * the few places in the rectangle. The callers filter on the ids with restrict() and keep their exact distance
 * check.
 */
final class PlacesInRectangle
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    /**
     * Places within the rectangle where a member has their active address.
     *
     * @return list<int>
     */
    public function withMembers(float $latitude, float $longitude, float $distance): array
    {
        return $this->find('INNER JOIN address a ON a.location = l.geoname_id AND a.active = 1', $latitude, $longitude, $distance);
    }

    /**
     * Places within the rectangle a trip leg goes to.
     *
     * @return list<int>
     */
    public function withLegs(float $latitude, float $longitude, float $distance): array
    {
        return $this->find('INNER JOIN sub_trips s ON s.location = l.geoname_id', $latitude, $longitude, $distance);
    }

    /**
     * Places within the rectangle an activity takes place at.
     *
     * @return list<int>
     */
    public function withActivities(float $latitude, float $longitude, float $distance): array
    {
        return $this->find('INNER JOIN activities act ON act.locationId = l.geoname_id', $latitude, $longitude, $distance);
    }

    /**
     * Limits the query to the places found ($field is the place, e.g. 'l.geonameId'); none found: no results.
     *
     * @param list<int> $placeIds
     */
    public static function restrict(QueryBuilder $queryBuilder, string $field, array $placeIds, string $parameter): void
    {
        if ([] === $placeIds) {
            $queryBuilder->andWhere('1 = 0');

            return;
        }

        $queryBuilder
            ->andWhere(\sprintf('%s IN (:%s)', $field, $parameter))
            ->setParameter($parameter, $placeIds, ArrayParameterType::INTEGER);
    }

    /**
     * @return list<int>
     */
    private function find(string $join, float $latitude, float $longitude, float $distance): array
    {
        $boundingBox = new GeoPoint($latitude, $longitude)->boundingBox($distance, 'km');

        $ids = $this->connection->fetchFirstColumn(
            'SELECT DISTINCT l.geoname_id FROM location l ' . $join
            . ' WHERE MBRContains(ST_Envelope(ST_GeomFromText(:rectangle, 0)), l.coordinates)',
            [
                'rectangle' => \sprintf(
                    'LINESTRING(%F %F, %F %F)',
                    $boundingBox->getMinLongitude(),
                    $boundingBox->getMinLatitude(),
                    $boundingBox->getMaxLongitude(),
                    $boundingBox->getMaxLatitude()
                ),
            ]
        );

        return array_map(intval(...), $ids);
    }
}
