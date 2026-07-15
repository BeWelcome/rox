<?php

namespace App\Tests\Model;

use App\Doctrine\LegOptionsType;
use App\Dto\LegDto;
use App\Dto\TripDto;
use DateTime;

/**
 * @SuppressWarnings("PHPMD.TooManyPublicMethods")
 */
class TripModelTest extends TripModelTestCase
{
    public function testConsecutiveDatesReturnNoErrors(): void
    {
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $leg1->arrival = new DateTime('2021-02-22');
        $leg1->departure = new DateTime('2021-02-24');
        $leg1->options = [LegOptionsType::MEET_LOCALS];
        $leg2 = new LegDto();
        $leg2->arrival = new DateTime('2021-02-24');
        $leg2->departure = new DateTime('2021-02-25');
        $leg2->options = [LegOptionsType::MEET_LOCALS];
        $leg3 = new LegDto();
        $leg3->arrival = new DateTime('2021-02-25');
        $leg3->departure = new DateTime('2021-02-26');
        $leg3->options = [LegOptionsType::MEET_LOCALS];
        $leg4 = new LegDto();
        $leg4->arrival = new DateTime('2021-02-26');
        $leg4->departure = new DateTime('2021-02-28');
        $leg4->options = [LegOptionsType::PRIVATE];
        $tripDto->legs->add($leg1);
        $tripDto->legs->add($leg2);
        $tripDto->legs->add($leg3);
        $tripDto->legs->add($leg4);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

        $this->assertSame(0, \count($errors));
    }

    public function testNonConsecutiveDatesReturnNoErrors(): void
    {
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $leg1->arrival = new DateTime('2021-02-22');
        $leg1->departure = new DateTime('2021-02-24');
        $leg1->options = [LegOptionsType::MEET_LOCALS];
        $leg2 = new LegDto();
        $leg2->arrival = new DateTime('2021-02-25');
        $leg2->departure = new DateTime('2021-02-27');
        $leg2->options = [LegOptionsType::PRIVATE];
        $leg3 = new LegDto();
        $leg3->arrival = new DateTime('2021-02-28');
        $leg3->departure = new DateTime('2021-03-02');
        $leg3->options = [LegOptionsType::PRIVATE];
        $leg4 = new LegDto();
        $leg4->arrival = new DateTime('2021-03-03');
        $leg4->departure = new DateTime('2021-03-28');
        $leg4->options = [LegOptionsType::PRIVATE];
        $tripDto->legs->add($leg1);
        $tripDto->legs->add($leg2);
        $tripDto->legs->add($leg3);
        $tripDto->legs->add($leg4);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

        $this->assertSame(0, \count($errors));
    }

    public function testOverlappingDatesTwoLegsReturnErrors(): void
    {
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $leg1->arrival = new DateTime('2021-02-22');
        $leg1->departure = new DateTime('2021-02-24');
        $leg2 = new LegDto();
        $leg2->arrival = new DateTime('2021-02-21');
        $leg2->departure = new DateTime('2021-02-23');
        $tripDto->legs->add($leg1);
        $tripDto->legs->add($leg2);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

        $this->assertNotSame(0, \count($errors));
        $this->assertTrue(isset($errors[0]['leg']));
        $this->assertSame($errors[0]['field'], 'duration');
        $this->assertTrue(isset($errors[1]['leg']));
        $this->assertSame($errors[1]['field'], 'duration');
    }

    public function testOverlappingDatesSeveralLegsReturnErrors(): void
    {
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $leg1->arrival = new DateTime('2021-02-22');
        $leg1->departure = new DateTime('2021-02-24');
        $leg2 = new LegDto();
        $leg2->arrival = new DateTime('2021-02-24');
        $leg2->departure = new DateTime('2021-02-25');
        $leg3 = new LegDto();
        $leg3->arrival = new DateTime('2021-02-22');
        $leg3->departure = new DateTime('2021-02-24');
        $tripDto->legs->add($leg1);
        $tripDto->legs->add($leg2);
        $tripDto->legs->add($leg3);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

        $this->assertNotSame(0, \count($errors));
        $this->assertTrue(isset($errors[0]['leg']));
        $this->assertSame($errors[0]['field'], 'duration');
        $this->assertTrue(isset($errors[1]['leg']));
        $this->assertSame($errors[1]['field'], 'duration');
    }

    public function testSeveralOverlappingLegsReturnErrors(): void
    {
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $leg1->arrival = new DateTime('2021-02-22');
        $leg1->departure = new DateTime('2021-02-24');
        $leg2 = new LegDto();
        $leg2->arrival = new DateTime('2021-02-24');
        $leg2->departure = new DateTime('2021-02-25');
        $leg3 = new LegDto();
        $leg3->arrival = new DateTime('2021-02-22');
        $leg3->departure = new DateTime('2021-02-24');
        $leg4 = new LegDto();
        $leg4->arrival = new DateTime('2021-01-22');
        $leg4->departure = new DateTime('2021-03-24');
        $tripDto->legs->add($leg1);
        $tripDto->legs->add($leg2);
        $tripDto->legs->add($leg3);
        $tripDto->legs->add($leg4);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

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
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $leg1->options = [LegOptionsType::MEET_LOCALS];
        $tripDto->legs->add($leg1);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

        $this->assertSame(0, \count($errors));
    }

    public function testSingleLegNoOptionsSelectedReturnsAnError(): void
    {
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $tripDto->legs->add($leg1);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

        $this->assertNotSame(0, \count($errors));
        $this->assertTrue(isset($errors[0]['leg']));
        $this->assertTrue(isset($errors[0]['field']));
        $this->assertTrue(isset($errors[0]['error']));
        $this->assertSame($errors[0]['error'], 'trip.error.no.options');
    }

    public function testMultipleLegNoOptionsSelectedReturnsAnError(): void
    {
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $leg2 = new LegDto();
        $tripDto->legs->add($leg1);
        $tripDto->legs->add($leg2);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

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
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $leg1->options = [LegOptionsType::LOOKING_FOR_HOST];
        $leg2 = new LegDto();
        $leg2->options = [LegOptionsType::LOOKING_FOR_HOST, LegOptionsType::MEET_LOCALS];
        $leg3 = new LegDto();
        $leg3->options = [LegOptionsType::MEET_LOCALS];
        $leg4 = new LegDto();
        $leg4->options = [LegOptionsType::PRIVATE];
        $tripDto->legs->add($leg1);
        $tripDto->legs->add($leg2);
        $tripDto->legs->add($leg3);
        $tripDto->legs->add($leg4);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

        $this->assertSame(0, \count($errors));
    }

    public function testLegsAreNotReturnedSortedOnCreateIfErrorsWereFound(): void
    {
        $tripDto = new TripDto();
        $leg1 = new LegDto();
        $leg1->arrival = new DateTime('2021-02-22');
        $leg1->departure = new DateTime('2021-02-24');
        $leg1->options = [LegOptionsType::MEET_LOCALS];
        $leg2 = new LegDto();
        $leg2->arrival = new DateTime('2021-02-24');
        $leg2->departure = new DateTime('2021-02-25');
        $leg2->options = [LegOptionsType::MEET_LOCALS];
        $leg3 = new LegDto();
        $leg3->arrival = new DateTime('2021-02-22');
        $leg3->departure = new DateTime('2021-02-24');
        $leg3->options = [LegOptionsType::LOOKING_FOR_HOST];
        $leg4 = new LegDto();
        $leg4->arrival = new DateTime('2021-01-22');
        $leg4->departure = new DateTime('2021-03-24');
        $leg4->options = [LegOptionsType::PRIVATE];
        $tripDto->legs->add($leg1);
        $tripDto->legs->add($leg2);
        $tripDto->legs->add($leg3);
        $tripDto->legs->add($leg4);

        $tripModel = $this->getTripModel();
        $errors = $tripModel->checkTripCreateOrEditData($tripDto);

        $this->assertNotSame(0, \count($errors));

        $legs = $tripDto->legs;
        $this->assertEquals(new DateTime('2021-02-22'), $legs[0]->arrival);
        $this->assertEquals(new DateTime('2021-02-24'), $legs[1]->arrival);
        $this->assertEquals(new DateTime('2021-02-22'), $legs[2]->arrival);
        $this->assertEquals(new DateTime('2021-01-22'), $legs[3]->arrival);
    }
}
