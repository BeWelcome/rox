<?php

namespace App\Tests\Repository;

use App\Entity\Leg;
use App\Entity\Location;
use App\Entity\Member;
use App\Entity\Trip;
use App\Repository\LegRepository;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('integration')]
class LegRepositoryTest extends KernelTestCase
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

        /** @var LegRepository $repository */
        $repository = $this->entityManager->getRepository(Leg::class);
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

    public function testHostsToNotifyLiveWithinTheInvitationRadiusOnly(): void
    {
        [$hostId, $travellerId] = $this->connection->fetchFirstColumn(<<<'SQL'
                SELECT m.id FROM member m JOIN address a ON a.member_id = m.id AND a.active = 1
                JOIN location l ON l.geoname_id = a.location
                WHERE m.Status = 'Active' AND m.MaxGuests >= 1
                ORDER BY m.id LIMIT 2
            SQL);
        self::assertNotNull($travellerId, 'the test fixtures contain two active members with an address');

        // the host wants to hear about trips right away
        $preferenceId = $this->connection->fetchOne("SELECT id FROM preferences WHERE codename = 'TripsNotifications'");
        $this->connection->executeStatement('DELETE FROM memberspreferences WHERE IdMember = ? AND IdPreference = ?', [$hostId, $preferenceId]);
        $this->connection->executeStatement(
            "INSERT INTO memberspreferences (IdMember, IdPreference, Value, updated, created) VALUES (?, ?, 'Immediately', NOW(), NOW())",
            [$hostId, $preferenceId]
        );

        $town = $this->connection->fetchAssociative(<<<'SQL'
                SELECT ST_Y(l.coordinates) AS latitude, ST_X(l.coordinates) AS longitude
                FROM address a JOIN location l ON l.geoname_id = a.location WHERE a.member_id = ? AND a.active = 1
            SQL, [$hostId]);

        /** @var LegRepository $repository */
        $repository = $this->entityManager->getRepository(Leg::class);
        $traveller = $this->entityManager->find(Member::class, $travellerId);

        // invitation radius 20 km: a leg 10 km north of the host's town reaches the host, one 22 km north doesn't
        $latitudeKm = 1 / 111.2;
        $trip = $this->createTrip($traveller, [
            [(float) $town['latitude'] + 10 * $latitudeKm, (float) $town['longitude']],
            [(float) $town['latitude'] + 22 * $latitudeKm, (float) $town['longitude']],
        ], 20);
        [$near, $far] = $trip->getLegs()->getValues();

        $notified = static fn (Leg $leg): array => array_map(static fn (Member $host): int => $host->getId(), $repository->getMembersToNotifyAboutLeg($leg));
        self::assertContains($hostId, $notified($near));
        self::assertNotContains($hostId, $notified($far));
    }

    /**
     * A host can host many legs: the host of one leg can be the host of the next (the leg's host was a one-to-one
     * mapping, and the second accepted invitation of a host failed on a unique index).
     */
    public function testAHostCanHostSeveralLegs(): void
    {
        [$hostId, $travellerId] = $this->connection->fetchFirstColumn("SELECT id FROM member WHERE Status = 'Active' ORDER BY id LIMIT 2");
        $host = $this->entityManager->find(Member::class, $hostId);
        $trip = $this->createTrip($this->entityManager->find(Member::class, $travellerId), [[48.1, 11.6], [52.5, 13.4]]);

        foreach ($trip->getLegs() as $leg) {
            $leg->setInvitedBy($host);
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        self::assertSame(2, (int) $this->connection->fetchOne('SELECT COUNT(*) FROM leg WHERE trip_id = ? AND invited_by_id = ?', [$trip->getId(), $hostId]));
    }

    /**
     * @param list<array{float, float}> $coordinates latitude and longitude of each leg
     */
    private function createTrip(Member $creator, array $coordinates, int $invitationRadius = 50): Trip
    {
        $trip = new Trip();
        $trip
            ->setSummary('Bounding box test')
            ->setDescription('Bounding box test')
            ->setCreated(new DateTime())
            ->setCreator($creator)
            ->setInvitationRadius($invitationRadius)
        ;

        $geonameId = (int) $this->connection->fetchOne('SELECT MAX(geoname_id) FROM location');
        foreach ($coordinates as [$latitude, $longitude]) {
            ++$geonameId;
            $this->connection->executeStatement(
                "INSERT INTO location (geoname_id, name, feature_class, coordinates)
                    VALUES (?, 'Bounding box test', 'P', ST_GeomFromText(?, 0))",
                [$geonameId, \sprintf('POINT(%F %F)', $longitude, $latitude)]
            );

            $leg = new Leg();
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
