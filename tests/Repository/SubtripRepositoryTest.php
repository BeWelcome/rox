<?php

namespace App\Tests\Repository;

use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Repository\SubtripRepository;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('integration')]
class SubtripRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $this->connection = $this->entityManager->getConnection();
        $this->connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        $this->connection->rollBack();
        parent::tearDown();
    }

    public function testVisitorsCountFindsLegsInEveryDirectionWithinTheRadiusOnly(): void
    {
        [$hostId, $travellerId] = $this->connection->fetchFirstColumn(<<<'SQL'
                SELECT m.id FROM member m JOIN address a ON a.member_id = m.id AND a.active = 1
                WHERE m.Status = 'Active' AND a.latitude IS NOT NULL AND a.longitude IS NOT NULL
                ORDER BY m.id LIMIT 2
            SQL);
        self::assertNotNull($travellerId, 'the test fixtures contain two active members with an address');

        /** @var SubtripRepository $repository */
        $repository = $this->entityManager->getRepository(Subtrip::class);
        $host = $this->entityManager->find(Member::class, $hostId);
        $traveller = $this->entityManager->find(Member::class, $travellerId);
        $address = $host->getActiveAddress();
        $latitude = (float) $address->getLatitude();
        $longitude = (float) $address->getLongitude();

        $before = $repository->getVisitorsCount($host, 20);

        // 18 km to the north, south, east and west (inside 20 km), 30 km to the north (outside)
        $latitudeKm = 1 / 111.2;
        $longitudeKm = 1 / (111.2 * cos(deg2rad($latitude)));
        $trip = $this->createTrip($traveller, [
            [$latitude + 18 * $latitudeKm, $longitude],
            [$latitude - 18 * $latitudeKm, $longitude],
            [$latitude, $longitude + 18 * $longitudeKm],
            [$latitude, $longitude - 18 * $longitudeKm],
            [$latitude + 30 * $latitudeKm, $longitude],
        ]);
        self::assertCount(5, $trip->getLegs());

        self::assertSame($before + 4, $repository->getVisitorsCount($host, 20));
    }

    /**
     * @param list<array{float, float}> $coordinates latitude and longitude of each leg
     */
    private function createTrip(Member $creator, array $coordinates): Trip
    {
        $trip = new Trip();
        $trip
            ->setSummary('Bounding box test')
            ->setDescription('Bounding box test')
            ->setCreated(new DateTime())
            ->setCreator($creator)
            ->setInvitationRadius(50)
        ;

        $geonameId = (int) $this->connection->fetchOne('SELECT MAX(geoname_id) FROM geo__names');
        foreach ($coordinates as [$latitude, $longitude]) {
            ++$geonameId;
            $this->connection->executeStatement(
                "INSERT INTO geo__names (geoname_id, name, feature_class, coordinates)
                    VALUES (?, 'Bounding box test', 'P', ST_GeomFromText(?, 0))",
                [$geonameId, \sprintf('POINT(%F %F)', $longitude, $latitude)]
            );

            $leg = new Subtrip();
            $leg
                ->setLocation($this->entityManager->find(Location::class, $geonameId))
                ->setArrival(new DateTime('+1 week'))
                ->setDeparture(new DateTime('+2 weeks'))
                ->setTrip($trip)
            ;
            $trip->addLeg($leg);
        }

        $this->entityManager->persist($trip);
        $this->entityManager->flush();

        return $trip;
    }
}
