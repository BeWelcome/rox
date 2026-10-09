<?php

namespace App\Tests\Model;

use App\Entity\Subtrip;
use App\Entity\Trip;
use DateTime;
use InvalidArgumentException;

class TripModelTestTripExpired extends TripModelTestCase
{
    public function testTripExpiredThrowsInvalidArgumentWithNoLegs()
    {
        $tripModel = $this->getTripModel();
        $trip = new Trip();

        $this->expectException(InvalidArgumentException::class);
        $tripModel->hasTripExpired($trip);
    }

    public function testTripExpiredOneLeg()
    {
        $tripModel = $this->getTripModel();
        $leg = new Subtrip();
        $leg->setDeparture(new DateTime('2020-01-01'));

        $trip = new Trip();
        $trip->addSubtrip($leg);

        $expired = $tripModel->hasTripExpired($trip);

        $this->assertTrue($expired);
    }

    public function testTripNotExpiredOneLeg()
    {
        $tripModel = $this->getTripModel();

        $tomorrow = new DateTime('+1day');
        $leg = new Subtrip();
        $leg->setDeparture($tomorrow);

        $trip = new Trip();
        $trip->addSubtrip($leg);

        $expired = $tripModel->hasTripExpired($trip);

        $this->assertFalse($expired);
    }

    public function testTripExpiredMultipleLegs()
    {
        $tripModel = $this->getTripModel();
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

        $expired = $tripModel->hasTripExpired($trip);

        $this->assertTrue($expired);
    }

    public function testTripNotExpiredMultipleLegs()
    {
        $tripModel = $this->getTripModel();

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

        $expired = $tripModel->hasTripExpired($trip);

        $this->assertFalse($expired);
    }

    public function testTripNotExpiredMultipleLegsPartlyInThePast()
    {
        $tripModel = $this->getTripModel();

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

        $expired = $tripModel->hasTripExpired($trip);

        $this->assertFalse($expired);
    }
}
