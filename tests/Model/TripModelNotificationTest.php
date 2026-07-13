<?php

namespace App\Tests\Model;

use App\Entity\Member;
use App\Entity\MemberLegHidden;
use App\Entity\MemberLegNotificationSent;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Model\TripModel;
use App\Repository\SubtripRepository;
use App\Repository\TripRepository;
use App\Service\Mailer;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class TripModelNotificationTest extends TestCase
{
    public function testMarkLegAsHiddenPersistsHiddenState(): void
    {
        $member = new Member();
        $leg = new Subtrip();

        $hiddenRepository = $this->createMock(EntityRepository::class);
        $hiddenRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['member' => $member, 'leg' => $leg])
            ->willReturn(null)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(MemberLegHidden::class)
            ->willReturn($hiddenRepository)
        ;
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (MemberLegHidden $hidden) use ($member, $leg): bool {
                return $hidden->getMember() === $member && $hidden->getLeg() === $leg;
            }))
        ;
        $entityManager->expects($this->once())->method('flush');

        $tripModel = new TripModel($entityManager);
        $tripModel->markLegAsHidden($member, $leg);
    }

    public function testMarkLegAsHiddenDoesNotDuplicateHiddenState(): void
    {
        $member = new Member();
        $leg = new Subtrip();
        $existingHidden = new MemberLegHidden($member, $leg);

        $hiddenRepository = $this->createMock(EntityRepository::class);
        $hiddenRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['member' => $member, 'leg' => $leg])
            ->willReturn($existingHidden)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(MemberLegHidden::class)
            ->willReturn($hiddenRepository)
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $tripModel = new TripModel($entityManager);
        $tripModel->markLegAsHidden($member, $leg);
    }

    public function testNotifyHostsAboutVisitorsSendsEmailsToMatchingHostsOnce(): void
    {
        [, $leg] = $this->createTripWithLeg();
        $host = new Member();
        $sameHost = new Member();
        $this->setEntityId($host, 12);
        $this->setEntityId($sameHost, 12);
        $legCalls = [];
        $finds = [];

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->willReturnMap([
                [Subtrip::class, $this->createSubtripRepository([$host, $sameHost], $legCalls)],
                [MemberLegNotificationSent::class, $this->createSentRepository(null, $finds)],
            ])
        ;
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (MemberLegNotificationSent $sent) use ($host, $leg): bool {
                return $sent->getMember() === $host && $sent->getLeg() === $leg;
            }))
        ;
        $entityManager->expects($this->once())->method('flush');
        $this->allowTripNotificationLocks($entityManager);

        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendTripNotificationEmail')
            ->with($host, $leg)
            ->willReturn(true)
        ;

        $tripModel = new TripModel($entityManager, $mailer);
        $tripModel->notifyHostsAboutVisitors($leg);

        $this->assertSame([
            [$leg, [Preference::TRIP_NOTIFICATIONS_IMMEDIATELY], 3],
        ], $legCalls);
        $this->assertCount(1, $finds);
    }

    public function testNotifyHostsAboutVisitorsDoesNotFlushWhenNoHostMatches(): void
    {
        [, $leg] = $this->createTripWithLeg();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Subtrip::class)
            ->willReturn($this->createSubtripRepository([]))
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $tripModel = new TripModel($entityManager);
        $tripModel->notifyHostsAboutVisitors($leg);
    }

    public function testNotifyHostsAboutVisitorsSkipsAlreadySentNotification(): void
    {
        [, $leg] = $this->createTripWithLeg();
        $host = new Member();
        $this->setEntityId($host, 12);
        $finds = [];

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->willReturnMap([
                [Subtrip::class, $this->createSubtripRepository([$host])],
                [MemberLegNotificationSent::class, $this->createSentRepository(new MemberLegNotificationSent($host, $leg), $finds)],
            ])
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');
        $this->allowTripNotificationLocks($entityManager);

        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->never())->method('sendTripNotificationEmail');

        $tripModel = new TripModel($entityManager, $mailer);
        $tripModel->notifyHostsAboutVisitors($leg);

        $this->assertCount(1, $finds);
    }

    public function testNotifyHostsAboutVisitorsSkipsHostWhenNotificationLockIsBusy(): void
    {
        [, $leg] = $this->createTripWithLeg();
        $host = new Member();
        $this->setEntityId($host, 12);
        $finds = [];

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->willReturnMap([
                [Subtrip::class, $this->createSubtripRepository([$host])],
                [MemberLegNotificationSent::class, $this->createSentRepository(null, $finds)],
            ])
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');
        $this->denyTripNotificationLocks($entityManager);

        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->never())->method('sendTripNotificationEmail');

        $tripModel = new TripModel($entityManager, $mailer);
        $tripModel->notifyHostsAboutVisitors($leg);

        $this->assertSame([], $finds);
    }

    public function testSendScheduledTripNotificationsUsesFrequencyAndCreatedWindow(): void
    {
        [$trip, $leg] = $this->createTripWithLeg();
        $host = new Member();
        $this->setEntityId($host, 12);
        $since = new DateTimeImmutable('2026-06-27 00:00:00');
        $until = new DateTimeImmutable('2026-06-28 00:00:00');
        $subtripCalls = [];

        $tripRepository = $this->createMock(TripRepository::class);
        $tripRepository
            ->expects($this->once())
            ->method('findTripsCreatedBetween')
            ->with($since, $until)
            ->willReturn([$trip])
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->willReturnMap([
                [Trip::class, $tripRepository],
                [Subtrip::class, $this->createSubtripRepository([$host], $subtripCalls)],
                [MemberLegNotificationSent::class, $this->createSentRepository()],
            ])
        ;
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');
        $this->allowTripNotificationLocks($entityManager);

        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendTripNotificationEmail')
            ->with($host, $leg)
            ->willReturn(true)
        ;

        $tripModel = new TripModel($entityManager, $mailer);
        $sent = $tripModel->sendScheduledTripNotifications(
            Preference::TRIP_NOTIFICATIONS_DAILY,
            $since,
            $until
        );

        $this->assertSame(1, $sent);
        $this->assertSame([
            [$leg, [Preference::TRIP_NOTIFICATIONS_DAILY], 3],
        ], $subtripCalls);
    }

    public function testSendScheduledTripNotificationsDoesNotStoreFailedSend(): void
    {
        [$trip, $leg] = $this->createTripWithLeg();
        $host = new Member();
        $this->setEntityId($host, 12);

        $tripRepository = $this->createStub(TripRepository::class);
        $tripRepository->method('findTripsCreatedBetween')->willReturn([$trip]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('getRepository')
            ->willReturnMap([
                [Trip::class, $tripRepository],
                [Subtrip::class, $this->createSubtripRepository([$host])],
                [MemberLegNotificationSent::class, $this->createSentRepository()],
            ])
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');
        $this->allowTripNotificationLocks($entityManager);

        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendTripNotificationEmail')
            ->with($host, $leg)
            ->willReturn(false)
        ;

        $tripModel = new TripModel($entityManager, $mailer);
        $sent = $tripModel->sendScheduledTripNotifications(
            Preference::TRIP_NOTIFICATIONS_WEEKLY,
            new DateTimeImmutable('2026-06-21 00:00:00'),
            new DateTimeImmutable('2026-06-28 00:00:00')
        );

        $this->assertSame(0, $sent);
    }

    public function testCopyTripDoesNotNotifyHostsAboutCopiedTrip(): void
    {
        $trip = new Trip();
        $trip->setSummary('Berlin visit');
        $leg = new Subtrip();
        $leg->setArrival(new DateTime('+1 week'));
        $leg->setDeparture(new DateTime('+2 weeks'));
        $trip->addLeg($leg);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->atLeast(2))->method('persist');
        $entityManager->expects($this->atLeast(2))->method('flush');

        $tripModel = new NotifyingTripModel($entityManager);
        $copiedTrip = $tripModel->copyTrip($trip);

        $this->assertSame('Berlin visit - copy', $copiedTrip->getSummary());
        $this->assertSame([], $tripModel->notifiedTrips);
    }

    private function createSentRepository(
        ?MemberLegNotificationSent $existing = null,
        array &$finds = [],
    ): EntityRepository {
        $repository = $this->createStub(EntityRepository::class);
        $repository
            ->method('findOneBy')
            ->willReturnCallback(static function (array $criteria) use ($existing, &$finds): ?MemberLegNotificationSent {
                $finds[] = $criteria;

                if (
                    null !== $existing
                    && $existing->getMember() === $criteria['member']
                    && $existing->getLeg() === $criteria['leg']
                ) {
                    return $existing;
                }

                return null;
            })
        ;

        return $repository;
    }

    /**
     * @param Member[] $hosts
     */
    private function createSubtripRepository(array $hosts, array &$calls = []): SubtripRepository
    {
        $repository = $this->createStub(SubtripRepository::class);
        $repository
            ->method('getMembersToNotifyAboutLeg')
            ->willReturnCallback(static function (
                Subtrip $subtrip,
                array $notificationValues = [Preference::TRIP_NOTIFICATIONS_IMMEDIATELY],
                int $duration = 3,
            ) use ($hosts, &$calls): array {
                $calls[] = [$subtrip, $notificationValues, $duration];

                return $hosts;
            })
        ;

        return $repository;
    }

    /**
     * @return array{Trip, Subtrip}
     */
    private function createTripWithLeg(): array
    {
        $trip = new Trip()->setCreator(new Member()->setUsername('traveller'));
        $leg = new Subtrip();
        $trip->addLeg($leg);
        $this->setEntityId($trip, 42);
        $this->setEntityId($leg, 7);

        return [$trip, $leg];
    }

    private function setEntityId(object $entity, int $id): void
    {
        $property = new ReflectionProperty($entity, 'id');
        $property->setValue($entity, $id);
    }

    private function allowTripNotificationLocks(EntityManagerInterface $entityManager): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchOne')->willReturn(1);
        $entityManager->method('getConnection')->willReturn($connection);
    }

    private function denyTripNotificationLocks(EntityManagerInterface $entityManager): void
    {
        $connection = $this->createStub(Connection::class);
        $connection->method('fetchOne')->willReturn(0);
        $entityManager->method('getConnection')->willReturn($connection);
    }
}
