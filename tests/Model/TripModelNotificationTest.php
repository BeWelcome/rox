<?php

namespace App\Tests\Model;

use App\Entity\Member;
use App\Entity\MemberTripNotificationSent;
use App\Entity\MemberSubtripRead;
use App\Entity\Preference;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Model\TripModel;
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
    public function testMarkSubtripAsReadPersistsReadState(): void
    {
        $member = new Member();
        $subtrip = new Subtrip();

        $readRepository = $this->createMock(EntityRepository::class);
        $readRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['member' => $member, 'subtrip' => $subtrip])
            ->willReturn(null)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(MemberSubtripRead::class)
            ->willReturn($readRepository)
        ;
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (MemberSubtripRead $read) use ($member, $subtrip): bool {
                return $read->getMember() === $member && $read->getSubtrip() === $subtrip;
            }))
        ;
        $entityManager->expects($this->once())->method('flush');

        $tripModel = new TripModel($entityManager);
        $tripModel->markSubtripAsRead($member, $subtrip);
    }

    public function testMarkSubtripAsReadDoesNotDuplicateReadState(): void
    {
        $member = new Member();
        $subtrip = new Subtrip();
        $existingRead = new MemberSubtripRead($member, $subtrip);

        $readRepository = $this->createMock(EntityRepository::class);
        $readRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['member' => $member, 'subtrip' => $subtrip])
            ->willReturn($existingRead)
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(MemberSubtripRead::class)
            ->willReturn($readRepository)
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $tripModel = new TripModel($entityManager);
        $tripModel->markSubtripAsRead($member, $subtrip);
    }

    public function testNotifyHostsAboutTripSendsEmailsToMatchingHostsOnce(): void
    {
        $creator = new Member()->setUsername('traveller');
        $host = new Member();
        $sameHost = new Member();
        $this->setEntityId($host, 12);
        $this->setEntityId($sameHost, 12);
        $trip = new Trip()->setCreator($creator);
        $this->setEntityId($trip, 42);

        $subtripRepository = $this->createSubtripRepository([$host, $sameHost]);
        $sentRepository = new SentTripNotificationsRepository();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                [Subtrip::class, $subtripRepository],
                [MemberTripNotificationSent::class, $sentRepository],
            ])
        ;
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (MemberTripNotificationSent $sent) use ($host, $trip): bool {
                return $sent->getMember() === $host && $sent->getTrip() === $trip;
            }))
        ;
        $entityManager->expects($this->once())->method('flush');
        $this->allowTripNotificationLocks($entityManager);
        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendTripNotificationEmail')
            ->with($host, $trip)
            ->willReturn(true)
        ;

        $tripModel = new TripModel($entityManager, $mailer);
        $tripModel->notifyHostsAboutTrip($trip);

        $this->assertSame([
            [$trip, [
                Preference::TRIP_NOTIFICATIONS_IMMEDIATELY,
            ], 3],
        ], $subtripRepository->calls);
        $this->assertCount(1, $sentRepository->finds);
    }

    public function testNotifyHostsAboutTripDoesNotFlushWhenNoHostMatches(): void
    {
        $trip = new Trip();
        $subtripRepository = $this->createSubtripRepository([]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Subtrip::class)
            ->willReturn($subtripRepository)
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $tripModel = new TripModel($entityManager);
        $tripModel->notifyHostsAboutTrip($trip);
    }

    public function testNotifyHostsAboutTripSkipsAlreadySentNotification(): void
    {
        $creator = new Member()->setUsername('traveller');
        $host = new Member();
        $this->setEntityId($host, 12);
        $trip = new Trip()->setCreator($creator);
        $this->setEntityId($trip, 42);

        $subtripRepository = $this->createSubtripRepository([$host]);
        $sentRepository = new SentTripNotificationsRepository([
            new MemberTripNotificationSent($host, $trip),
        ]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                [Subtrip::class, $subtripRepository],
                [MemberTripNotificationSent::class, $sentRepository],
            ])
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');
        $this->allowTripNotificationLocks($entityManager);
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->never())->method('sendTripNotificationEmail');

        $tripModel = new TripModel($entityManager, $mailer);
        $tripModel->notifyHostsAboutTrip($trip);

        $this->assertCount(1, $sentRepository->finds);
    }

    public function testNotifyHostsAboutTripSkipsHostWhenNotificationLockIsBusy(): void
    {
        $creator = new Member()->setUsername('traveller');
        $host = new Member();
        $this->setEntityId($host, 12);
        $trip = new Trip()->setCreator($creator);
        $this->setEntityId($trip, 42);

        $subtripRepository = $this->createSubtripRepository([$host]);
        $sentRepository = new SentTripNotificationsRepository();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                [Subtrip::class, $subtripRepository],
                [MemberTripNotificationSent::class, $sentRepository],
            ])
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');
        $this->denyTripNotificationLocks($entityManager);
        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->never())->method('sendTripNotificationEmail');

        $tripModel = new TripModel($entityManager, $mailer);
        $tripModel->notifyHostsAboutTrip($trip);

        $this->assertSame([], $sentRepository->finds);
    }

    public function testSendScheduledTripNotificationsUsesFrequencyAndCreatedWindow(): void
    {
        $creator = new Member()->setUsername('traveller');
        $host = new Member();
        $this->setEntityId($host, 12);
        $trip = new Trip()->setCreator($creator);
        $this->setEntityId($trip, 42);
        $since = new DateTimeImmutable('2026-06-27 00:00:00');
        $until = new DateTimeImmutable('2026-06-28 00:00:00');

        $tripRepository = new CreatedTripsRepository([$trip]);
        $subtripRepository = $this->createSubtripRepository([$host]);
        $sentRepository = new SentTripNotificationsRepository();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(3))
            ->method('getRepository')
            ->willReturnMap([
                [Trip::class, $tripRepository],
                [Subtrip::class, $subtripRepository],
                [MemberTripNotificationSent::class, $sentRepository],
            ])
        ;
        $entityManager->expects($this->once())->method('persist');
        $entityManager->expects($this->once())->method('flush');
        $this->allowTripNotificationLocks($entityManager);
        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendTripNotificationEmail')
            ->with($host, $trip)
            ->willReturn(true)
        ;

        $tripModel = new TripModel($entityManager, $mailer);
        $sent = $tripModel->sendScheduledTripNotifications(
            Preference::TRIP_NOTIFICATIONS_DAILY,
            $since,
            $until
        );

        $this->assertSame(1, $sent);
        $this->assertSame($since, $tripRepository->since);
        $this->assertSame($until, $tripRepository->until);
        $this->assertSame([
            [$trip, [Preference::TRIP_NOTIFICATIONS_DAILY], 3],
        ], $subtripRepository->calls);
    }

    public function testSendScheduledTripNotificationsDoesNotStoreFailedSend(): void
    {
        $creator = new Member()->setUsername('traveller');
        $host = new Member();
        $this->setEntityId($host, 12);
        $trip = new Trip()->setCreator($creator);
        $this->setEntityId($trip, 42);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(3))
            ->method('getRepository')
            ->willReturnMap([
                [Trip::class, new CreatedTripsRepository([$trip])],
                [Subtrip::class, $this->createSubtripRepository([$host])],
                [MemberTripNotificationSent::class, new SentTripNotificationsRepository()],
            ])
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');
        $this->allowTripNotificationLocks($entityManager);
        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendTripNotificationEmail')
            ->with($host, $trip)
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
        $trip->addSubtrip($leg);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->atLeast(2))->method('persist');
        $entityManager->expects($this->atLeast(2))->method('flush');

        $tripModel = new NotifyingTripModel($entityManager);
        $copiedTrip = $tripModel->copyTrip($trip);

        $this->assertSame('Berlin visit - copy', $copiedTrip->getSummary());
        $this->assertSame([], $tripModel->notifiedTrips);
    }

    private function setEntityId(object $entity, int $id): void
    {
        $property = new ReflectionProperty($entity, 'id');
        $property->setValue($entity, $id);
    }

    private function createSubtripRepository(array $hosts): MatchingHostsRepository
    {
        return new MatchingHostsRepository($hosts);
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
