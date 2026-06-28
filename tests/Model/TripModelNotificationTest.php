<?php

namespace App\Tests\Model;

use App\Entity\Member;
use App\Entity\MemberSubtripRead;
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

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Subtrip::class)
            ->willReturn($subtripRepository)
        ;
        $entityManager->expects($this->never())->method('persist');
        $entityManager->expects($this->never())->method('flush');
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
