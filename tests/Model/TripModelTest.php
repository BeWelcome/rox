<?php

namespace App\Tests\Model;

use App\Doctrine\SubtripOptionsType;
use App\Entity\Subtrip;
use App\Entity\Trip;
use App\Model\TripModel;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class TripModelTest extends TripModelTestCase
{
    public function testConsecutiveDatesReturnNoErrors(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $leg1->setArrival(new DateTime('2021-02-22'));
        $leg1->setDeparture(new DateTime('2021-02-24'));
        $leg1->setOptions([SubtripOptionsType::MEET_LOCALS]);
        $leg2 = new SubTrip();
        $leg2->setArrival(new DateTime('2021-02-24'));
        $leg2->setDeparture(new DateTime('2021-02-25'));
        $leg2->setOptions([SubtripOptionsType::MEET_LOCALS]);
        $leg3 = new SubTrip();
        $leg3->setArrival(new DateTime('2021-02-25'));
        $leg3->setDeparture(new DateTime('2021-02-26'));
        $leg3->setOptions([SubtripOptionsType::MEET_LOCALS]);
        $leg4 = new SubTrip();
        $leg4->setArrival(new DateTime('2021-02-26'));
        $leg4->setDeparture(new DateTime('2021-02-28'));
        $leg4->setOptions([SubtripOptionsType::PRIVATE]);
        $trip
            ->addSubtrip($leg1)
            ->addSubtrip($leg2)
            ->addSubtrip($leg3)
            ->addSubtrip($leg4)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertSame(0, \count($errors));
    }

    public function testNonConsecutiveDatesReturnNoErrors(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $leg1->setArrival(new DateTime('2021-02-22'));
        $leg1->setDeparture(new DateTime('2021-02-24'));
        $leg1->setOptions([SubtripOptionsType::MEET_LOCALS]);
        $leg2 = new SubTrip();
        $leg2->setArrival(new DateTime('2021-02-25'));
        $leg2->setDeparture(new DateTime('2021-02-27'));
        $leg2->setOptions([SubtripOptionsType::PRIVATE]);
        $leg3 = new SubTrip();
        $leg3->setArrival(new DateTime('2021-02-28'));
        $leg3->setDeparture(new DateTime('2021-03-02'));
        $leg3->setOptions([SubtripOptionsType::PRIVATE]);
        $leg4 = new SubTrip();
        $leg4->setArrival(new DateTime('2021-03-03'));
        $leg4->setDeparture(new DateTime('2021-03-28'));
        $leg4->setOptions([SubtripOptionsType::PRIVATE]);
        $trip
            ->addSubtrip($leg1)
            ->addSubtrip($leg2)
            ->addSubtrip($leg3)
            ->addSubtrip($leg4)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertSame(0, \count($errors));
    }

    public function testOverlappingDatesTwoLegsReturnErrors(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $leg1->setArrival(new DateTime('2021-02-22'));
        $leg1->setDeparture(new DateTime('2021-02-24'));
        $leg2 = new SubTrip();
        $leg2->setArrival(new DateTime('2021-02-21'));
        $leg2->setDeparture(new DateTime('2021-02-23'));
        $trip
            ->addSubtrip($leg1)
            ->addSubtrip($leg2)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertNotSame(0, \count($errors));
        $this->assertTrue(isset($errors[0]['leg']));
        $this->assertSame($errors[0]['field'], 'duration');
        $this->assertTrue(isset($errors[1]['leg']));
        $this->assertSame($errors[1]['field'], 'duration');
    }

    public function testOverlappingDatesSeveralLegsReturnErrors(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $leg1->setArrival(new DateTime('2021-02-22'));
        $leg1->setDeparture(new DateTime('2021-02-24'));
        $leg2 = new SubTrip();
        $leg2->setArrival(new DateTime('2021-02-24'));
        $leg2->setDeparture(new DateTime('2021-02-25'));
        $leg3 = new SubTrip();
        $leg3->setArrival(new DateTime('2021-02-22'));
        $leg3->setDeparture(new DateTime('2021-02-24'));
        $trip
            ->addSubtrip($leg1)
            ->addSubtrip($leg2)
            ->addSubtrip($leg3)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertNotSame(0, \count($errors));
        $this->assertTrue(isset($errors[0]['leg']));
        $this->assertSame($errors[0]['field'], 'duration');
        $this->assertTrue(isset($errors[1]['leg']));
        $this->assertSame($errors[1]['field'], 'duration');
    }

    public function testSeveralOverlappingLegsReturnErrors(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $leg1->setArrival(new DateTime('2021-02-22'));
        $leg1->setDeparture(new DateTime('2021-02-24'));
        $leg2 = new SubTrip();
        $leg2->setArrival(new DateTime('2021-02-24'));
        $leg2->setDeparture(new DateTime('2021-02-25'));
        $leg3 = new SubTrip();
        $leg3->setArrival(new DateTime('2021-02-22'));
        $leg3->setDeparture(new DateTime('2021-02-24'));
        $leg4 = new SubTrip();
        $leg4->setArrival(new DateTime('2021-01-22'));
        $leg4->setDeparture(new DateTime('2021-03-24'));
        $trip
            ->addSubtrip($leg1)
            ->addSubtrip($leg2)
            ->addSubtrip($leg3)
            ->addSubtrip($leg4)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertNotSame(0, \count($errors));
        $this->assertTrue(isset($errors[0]['leg']));
        $this->assertSame($errors[0]['field'], 'duration');
        $this->assertTrue(isset($errors[2]['leg']));
        $this->assertSame($errors[2]['field'], 'duration');
        $this->assertTrue(isset($errors[3]['leg']));
        $this->assertSame($errors[3]['field'], 'duration');
    }

    public function testSingleLegWithOptionsSelectedReturnsNoError(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $leg1->setOptions([SubtripOptionsType::MEET_LOCALS]);
        $trip
            ->addSubtrip($leg1)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertSame(0, \count($errors));
    }

    public function testSingleLegNoOptionsSelectedReturnsAnError(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $trip
            ->addSubtrip($leg1)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertNotSame(0, \count($errors));
        $this->assertTrue(isset($errors[0]['leg']));
        $this->assertTrue(isset($errors[0]['field']));
        $this->assertTrue(isset($errors[0]['error']));
        $this->assertSame($errors[0]['error'], 'trip.error.no.options');
    }

    public function testMultipleLegNoOptionsSelectedReturnsAnError(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $leg2 = new SubTrip();
        $trip
            ->addSubtrip($leg1)
            ->addSubtrip($leg2)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertSame(2, \count($errors));
        $this->assertTrue(isset($errors[0]['leg']));
        $this->assertTrue(isset($errors[0]['error']));
        $this->assertSame($errors[0]['error'], 'trip.error.no.options');
        $this->assertTrue(isset($errors[1]['leg']));
        $this->assertTrue(isset($errors[1]['error']));
        $this->assertSame($errors[1]['error'], 'trip.error.no.options');
    }

    public function testMultipleLegWithOptionsSelectedReturnsNoError(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $leg1->setOptions([SubtripOptionsType::LOOKING_FOR_HOST]);
        $leg2 = new SubTrip();
        $leg2->setOptions([SubtripOptionsType::LOOKING_FOR_HOST, SubtripOptionsType::MEET_LOCALS]);
        $leg3 = new SubTrip();
        $leg3->setOptions([SubtripOptionsType::MEET_LOCALS]);
        $leg4 = new SubTrip();
        $leg4->setOptions([SubtripOptionsType::PRIVATE]);
        $trip
            ->addSubtrip($leg1)
            ->addSubtrip($leg2)
            ->addSubtrip($leg3)
            ->addSubtrip($leg4)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertSame(0, \count($errors));
    }

    public function testLegsAreNotReturnedSortedOnCreateIfErrorsWereFound(): void
    {
        $trip = new Trip();
        $leg1 = new SubTrip();
        $leg1->setArrival(new DateTime('2021-02-22'));
        $leg1->setDeparture(new DateTime('2021-02-24'));
        $leg1->setOptions([SubtripOptionsType::MEET_LOCALS]);
        $leg2 = new SubTrip();
        $leg2->setArrival(new DateTime('2021-02-24'));
        $leg2->setDeparture(new DateTime('2021-02-25'));
        $leg2->setOptions([SubtripOptionsType::MEET_LOCALS]);
        $leg3 = new SubTrip();
        $leg3->setArrival(new DateTime('2021-02-22'));
        $leg3->setDeparture(new DateTime('2021-02-24'));
        $leg3->setOptions([SubtripOptionsType::LOOKING_FOR_HOST]);
        $leg4 = new SubTrip();
        $leg4->setArrival(new DateTime('2021-01-22'));
        $leg4->setDeparture(new DateTime('2021-03-24'));
        $leg4->setOptions([SubtripOptionsType::PRIVATE]);
        $trip
            ->addSubtrip($leg1)
            ->addSubtrip($leg2)
            ->addSubtrip($leg3)
            ->addSubtrip($leg4)
        ;

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($trip);

        $this->assertNotSame(0, \count($errors));

        $legs = $trip->getSubtrips();
        $this->assertEquals(new DateTime('2021-02-22'), $legs[0]->getArrival());
        $this->assertEquals(new DateTime('2021-02-24'), $legs[1]->getArrival());
        $this->assertEquals(new DateTime('2021-02-22'), $legs[2]->getArrival());
        $this->assertEquals(new DateTime('2021-01-22'), $legs[3]->getArrival());
    }
}
