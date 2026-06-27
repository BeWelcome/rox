<?php

namespace App\Tests\Model;

use App\Entity\Member;
use App\Entity\MemberTripRead;
use App\Entity\Notification;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Model\TripModel;
use App\Service\Mailer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;

class TripModelNotificationTest extends TestCase
{
    public function testMarkTripAsReadPersistsReadState(): void
    {
        $member = new Member();
        $trip = new Trip();
        $this->setEntityId($trip, 42);

        $readRepository = $this->createMock(EntityRepository::class);
        $readRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['member' => $member, 'trip' => $trip])
            ->willReturn(null)
        ;
        $notificationRepository = $this->createMock(EntityRepository::class);
        $notificationRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([])
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                [MemberTripRead::class, $readRepository],
                [Notification::class, $notificationRepository],
            ])
        ;
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (MemberTripRead $read) use ($member, $trip): bool {
                return $read->getMember() === $member && $read->getTrip() === $trip;
            }))
        ;
        $entityManager->expects($this->once())->method('flush');

        $tripModel = new TripModel($entityManager);
        $tripModel->markTripAsRead($member, $trip);
    }

    public function testMarkTripAsReadDoesNotDuplicateReadState(): void
    {
        $member = new Member();
        $trip = new Trip();
        $this->setEntityId($trip, 42);
        $existingRead = new MemberTripRead($member, $trip);

        $readRepository = $this->createMock(EntityRepository::class);
        $readRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['member' => $member, 'trip' => $trip])
            ->willReturn($existingRead)
        ;
        $notificationRepository = $this->createMock(EntityRepository::class);
        $notificationRepository
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([])
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                [MemberTripRead::class, $readRepository],
                [Notification::class, $notificationRepository],
            ])
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');

        $tripModel = new TripModel($entityManager);
        $tripModel->markTripAsRead($member, $trip);
    }

    public function testMarkTripAsReadChecksTripNotifications(): void
    {
        $member = new Member();
        $trip = new Trip();
        $this->setEntityId($trip, 42);
        $existingRead = new MemberTripRead($member, $trip);
        $notification = new Notification()->setChecked(false);

        $readRepository = $this->createMock(EntityRepository::class);
        $readRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['member' => $member, 'trip' => $trip])
            ->willReturn($existingRead)
        ;
        $notificationRepository = $this->createMock(EntityRepository::class);
        $notificationRepository
            ->expects($this->once())
            ->method('findBy')
            ->with([
                'member' => $member,
                'type' => 'trip',
                'link' => '/trip/42',
                'checked' => false,
            ])
            ->willReturn([$notification])
        ;

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->willReturnMap([
                [MemberTripRead::class, $readRepository],
                [Notification::class, $notificationRepository],
            ])
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->once())->method('flush');

        $tripModel = new TripModel($entityManager);
        $tripModel->markTripAsRead($member, $trip);

        $this->assertTrue($notification->getChecked());
    }

    public function testNotifyHostsAboutTripCreatesUnreadNotificationsForMatchingHosts(): void
    {
        $creator = new Member()->setUsername('traveller');
        $host = new Member();
        $trip = new Trip()->setCreator($creator);
        $this->setEntityId($trip, 42);

        $subtripRepository = $this->createSubtripRepository([$host]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Subtrip::class)
            ->willReturn($subtripRepository)
        ;
        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->callback(static function (Notification $notification) use ($creator, $host): bool {
                return $notification->getMember() === $host
                    && $notification->getRelMember() === $creator
                    && 'trip.notification.new' === $notification->getWordcode()
                    && '/trip/42' === $notification->getLink()
                    && false === $notification->getChecked();
            }))
        ;
        $entityManager->expects($this->once())->method('flush');
        $mailer = $this->createMock(Mailer::class);
        $mailer
            ->expects($this->once())
            ->method('sendTripNotificationEmail')
            ->with($host, $trip)
            ->willReturn(true)
        ;

        $tripModel = new TripModel($entityManager, $mailer);
        $tripModel->notifyHostsAboutTrip($trip);
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

    private function createSubtripRepository(array $hosts): EntityRepository
    {
        return new MatchingHostsRepository($hosts);
    }
}
