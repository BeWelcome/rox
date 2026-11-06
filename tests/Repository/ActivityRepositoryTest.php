<?php

namespace App\Tests\Repository;

use App\Entity\Activity;
use App\Entity\Location;
use App\Entity\Member;
use App\Repository\ActivityRepository;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

#[Group('integration')]
class ActivityRepositoryTest extends KernelTestCase
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

    public function testActivitiesNearAMemberAreTheOnesWithinTheRadiusOnly(): void
    {
        [$member, $near, $far] = $this->createActivitiesAroundAMember();

        /** @var ActivityRepository $repository */
        $repository = $this->entityManager->getRepository(Activity::class);
        $found = array_map(static fn (Activity $activity): int => $activity->getId(), iterator_to_array($repository->findNearMember($member, 20, 1, 100)->getCurrentPageResults()));

        self::assertContains($near->getId(), $found);
        self::assertNotContains($far->getId(), $found);
    }

    public function testUpcomingActivitiesAroundAMemberAreTheOnesWithinTheirRadiusOnly(): void
    {
        // the member's radius for activities near them is the default of 25 km
        [$member, $near, $far] = $this->createActivitiesAroundAMember();

        /** @var ActivityRepository $repository */
        $repository = $this->entityManager->getRepository(Activity::class);
        $found = array_map(static fn (Activity $activity): int => $activity->getId(), $repository->findUpcomingAroundLocation($member, false, 100));

        self::assertContains($near->getId(), $found);
        self::assertNotContains($far->getId(), $found);
    }

    /**
     * A member with an address and two activities next week: 10 km and 40 km to the north of them.
     *
     * @return array{Member, Activity, Activity}
     */
    private function createActivitiesAroundAMember(): array
    {
        $memberId = $this->connection->fetchOne(<<<'SQL'
                SELECT m.id FROM member m JOIN address a ON a.member_id = m.id AND a.active = 1
                WHERE m.Status = 'Active' AND a.latitude IS NOT NULL AND a.longitude IS NOT NULL
                  AND NOT EXISTS (SELECT 1 FROM memberspreferences mp JOIN preferences p ON p.id = mp.IdPreference
                                  WHERE mp.IdMember = m.id AND p.codename = 'ActivitiesNearMeRadius')
                ORDER BY m.id LIMIT 1
            SQL);
        self::assertNotFalse($memberId, 'the test fixtures contain an active member with an address');

        $member = $this->entityManager->find(Member::class, $memberId);
        $address = $member->getActiveAddress();
        $latitude = (float) $address->getLatitude();
        $longitude = (float) $address->getLongitude();

        $geonameId = (int) $this->connection->fetchOne('SELECT MAX(geoname_id) FROM location');
        $activities = [];
        foreach ([10, 40] as $kilometres) {
            ++$geonameId;
            $this->connection->executeStatement(
                "INSERT INTO location (geoname_id, name, feature_class, coordinates)
                    VALUES (?, 'Rectangle test', 'P', ST_GeomFromText(?, 0))",
                [$geonameId, \sprintf('POINT(%F %F)', $longitude, $latitude + $kilometres / 111.2)]
            );

            $activity = new Activity();
            $activity
                ->setCreatedBy($member)
                ->setTitle('Rectangle test ' . $kilometres . ' km')
                ->setDescription('Rectangle test')
                ->setStarts(new DateTime('+1 week'))
                ->setEnds(new DateTime('+1 week +2 hours'))
                ->setLocation($this->entityManager->find(Location::class, $geonameId))
                ->setStatus(Activity::STATUS_ACTIVE)
                ->setOnline(0)
            ;
            $this->entityManager->persist($activity);
            $activities[] = $activity;
        }
        $this->entityManager->flush();

        return [$member, ...$activities];
    }
}
