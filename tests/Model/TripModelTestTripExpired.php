<?php

namespace App\Tests\Model;

use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Model\TripModel;
use DateTime;
use Doctrine\ORM\EntityManager;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TripModelTestTripExpired extends TripModelTestCase
{
    public function testTripExpiredThrowsInvalidArgumentWithNoLegs(): void
    {
        $trip = new Trip();

        $tripModel = $this->getTripModel();
        $this->expectException(InvalidArgumentException::class);
        $tripModel->hasTripExpired($trip);
    }

    public function testTripExpiredOneLeg(): void
    {
        $leg = new Subtrip();
        $leg->setDeparture(new DateTime('2020-01-01'));

        $trip = new Trip();
        $trip->addSubtrip($leg);

        $tripModel = $this->getTripModel();
        $expired = $tripModel->hasTripExpired($trip);

        $this->assertTrue($expired);
    }

    public function testTripNotExpiredOneLeg(): void
    {
        $tomorrow = new DateTime('+1day');
        $leg = new Subtrip();
        $leg->setDeparture($tomorrow);

        $trip = new Trip();
        $trip->addSubtrip($leg);

        $tripModel = $this->getTripModel();
        $expired = $tripModel->hasTripExpired($trip);

        $this->assertFalse($expired);
    }

    public function testTripExpiredMultipleLegs(): void
    {
        $firstLeg = new Subtrip();
        $firstLeg->setDeparture(new DateTime('2020-01-01'));
        $secondLeg = new Subtrip();
        $secondLeg->setDeparture(new DateTime('2020-01-02'));
        $thirdLeg = new Subtrip();
        $thirdLeg->setDeparture(new DateTime('2020-01-03'));

        $trip = new Trip();
        $trip
            ->addSubtrip($firstLeg)
            ->addSubtrip($secondLeg)
            ->addSubtrip($thirdLeg)
        ;

        $tripModel = $this->getTripModel();
        $expired = $tripModel->hasTripExpired($trip);

        $this->assertTrue($expired);
    }

    public function testTripNotExpiredMultipleLegs(): void
    {
        $tomorrow = new DateTime('+1day');
        $theDayAfterTomorrow = new DateTime('+2days');
        $theNextDayAfterTheDayAfterTomorrow = new DateTime('+3days');
        $firstLeg = new Subtrip();
        $firstLeg->setDeparture($tomorrow);
        $secondLeg = new Subtrip();
        $secondLeg->setDeparture($theDayAfterTomorrow);
        $thirdLeg = new Subtrip();
        $thirdLeg->setDeparture($theNextDayAfterTheDayAfterTomorrow);

        $trip = new Trip();
        $trip
            ->addSubtrip($firstLeg)
            ->addSubtrip($secondLeg)
            ->addSubtrip($thirdLeg)
        ;

        $tripModel = $this->getTripModel();
        $expired = $tripModel->hasTripExpired($trip);

        $this->assertFalse($expired);
    }

    public function testTripNotExpiredMultipleLegsPartlyInThePast(): void
    {
        $yesterday = new DateTime('-1day');
        $theDayAfterTomorrow = new DateTime('+2days');
        $theNextDayAfterTheDayAfterTomorrow = new DateTime('+3days');
        $firstLeg = new Subtrip();
        $firstLeg->setDeparture($yesterday);
        $secondLeg = new Subtrip();
        $secondLeg->setDeparture($theDayAfterTomorrow);
        $thirdLeg = new Subtrip();
        $thirdLeg->setDeparture($theNextDayAfterTheDayAfterTomorrow);

        $trip = new Trip();
        $trip
            ->addSubtrip($firstLeg)
            ->addSubtrip($secondLeg)
            ->addSubtrip($thirdLeg)
        ;

        $tripModel = $this->getTripModel();
        $expired = $tripModel->hasTripExpired($trip);

        $this->assertFalse($expired);
    }
}
