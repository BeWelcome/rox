<?php

namespace App\Tests\Repository;

use App\Entity\Location;
use App\Repository\PlacesInRectangle;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('integration')]
class PlacesInRectangleTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
    }

    public function testFindsThePlaceOfAMemberInsideTheRectangleOnly(): void
    {
        $place = $this->connection->fetchAssociative(<<<'SQL'
                SELECT l.geoname_id, ST_Y(l.coordinates) AS latitude, ST_X(l.coordinates) AS longitude
                FROM location l JOIN address a ON a.location = l.geoname_id AND a.active = 1
                ORDER BY l.geoname_id LIMIT 1
            SQL);
        self::assertNotFalse($place, 'the test fixtures contain a member with an active address');

        $places = new PlacesInRectangle($this->connection);
        $latitude = (float) $place['latitude'];
        $longitude = (float) $place['longitude'];

        self::assertContains((int) $place['geoname_id'], $places->withMembers($latitude, $longitude, 20));
        // 30 km to the north is outside a 20 km rectangle
        self::assertNotContains((int) $place['geoname_id'], $places->withMembers($latitude + 30 / 111.2, $longitude, 20));
    }

    public function testRestrictWithoutPlacesFindsNothing(): void
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('l.geonameId')
            ->from(Location::class, 'l');
        PlacesInRectangle::restrict($queryBuilder, 'l.geonameId', [], 'places');

        self::assertSame([], $queryBuilder->getQuery()->setMaxResults(1)->getResult());
    }

    public function testRestrictLimitsTheQueryToThePlaces(): void
    {
        $ids = array_map(intval(...), $this->connection->fetchFirstColumn('SELECT geoname_id FROM location ORDER BY geoname_id LIMIT 2'));
        self::assertCount(2, $ids, 'the test fixtures contain places');

        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('l.geonameId')
            ->from(Location::class, 'l');
        PlacesInRectangle::restrict($queryBuilder, 'l.geonameId', [$ids[1]], 'places');

        self::assertSame([$ids[1]], array_column($queryBuilder->getQuery()->getScalarResult(), 'geonameId'));
    }
}
