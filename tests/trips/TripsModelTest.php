<?php

class TripsModelTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyVariables()
    {
        $vars = array();
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorNameEmpty', $errors);
        $this->assertContains('TripErrorDescriptionEmpty', $errors);
        $this->assertContains('TripErrorNoLocationSpecified', $errors);

        $this->assertEquals(1, count($tripInfo['locations']));
    }

    public function testEmptyName()
    {
        $vars = array(
            'trip-title' => '',
            'trip-description' => 'Trip Description'
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorNameEmpty', $errors);
        $this->assertContains('TripErrorNoLocationSpecified', $errors);

        $this->assertEquals(1, count($tripInfo['locations']));
    }

    public function testEmptyDescription()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => ''
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorDescriptionEmpty', $errors);
        $this->assertContains('TripErrorNoLocationSpecified', $errors);

        $this->assertEquals(1, count($tripInfo['locations']));
    }

    public function testEmptyLocations()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description'
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);
        $this->assertContains('TripErrorNoLocationSpecified', $errors);
        $this->assertEquals(1, count($tripInfo['locations']));
    }

    public function testAllFieldsEmpty() {
        $vars = array(
            'trip-title' => '',
            'trip-description' => '',
            'location-geoname-id' => array(''),
            'location-latitude' => array(''),
            'location-longitude' => array(''),
            'location' => array(''),
            'location-start-date' => array(''),
            'location-end-date' => array(''),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorNameEmpty', $errors);
        $this->assertContains('TripErrorDescriptionEmpty', $errors);
        $this->assertContains('TripErrorNoLocationSpecified', $errors);

        $this->assertEquals(1, count($tripInfo['locations']));
    }

    public function testWrongStartDateFormat()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507'),
            'location-latitude' => array('48.8534100'),
            'location-longitude' => array('2.3488000'),
            'location' => array('Paris'),
            'location-start-date' => array('2014-01-105'),
            'location-end-date' => array('2014-01-07'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorWrongStartDateFormat###0', $errors);
        $this->assertEquals(2, count($tripInfo['locations']));
        $this->assertEquals(false, $tripInfo['locations'][0]->startDate);
    }

    public function testWrongEndDateFormat()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507'),
            'location-latitude' => array('48.8534100'),
            'location-longitude' => array('2.3488000'),
            'location' => array('Paris'),
            'location-start-date' => array('2014-01-05'),
            'location-end-date' => array('2014-01-107'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorWrongEndDateFormat###0', $errors);
        $this->assertEquals(2, count($tripInfo['locations']));
        $this->assertEquals(false, $tripInfo['locations'][0]->endDate);
    }


    public function testWrongStartAndEndDateFormat()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507'),
            'location-latitude' => array('48.8534100'),
            'location-longitude' => array('2.3488000'),
            'location' => array('Paris'),
            'location-start-date' => array('2014-01-105'),
            'location-end-date' => array('2014-01-107'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorWrongStartDateFormat###0', $errors);
        $this->assertContains('TripErrorWrongEndDateFormat###0', $errors);
        $this->assertEquals(2, count($tripInfo['locations']));
        $this->assertEquals(false, $tripInfo['locations'][0]->startDate);
        $this->assertEquals(false, $tripInfo['locations'][0]->endDate);
    }

    public function testWrongStartAndEndDateFormatDifferentRows()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', '2643743'),
            'location-latitude' => array('48.8534100', '51.5085300'),
            'location-longitude' => array('2.3488000', '-0.1257400'),
            'location' => array('Paris', 'London'),
            'location-start-date' => array('', '2014-01-08'),
            'location-end-date' => array('2014-01-05', 'WrongEndDate'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorWrongStartDateFormat###0', $errors);
        $this->assertContains('TripErrorWrongEndDateFormat###1', $errors);
        $this->assertEquals(3, count($tripInfo['locations']));
        $this->assertEquals(false, $tripInfo['locations'][0]->startDate);
        $this->assertEquals(false, $tripInfo['locations'][1]->endDate);
    }

    public function testRemovedEmptyRow()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', ''),
            'location-latitude' => array('48.8534100', ''),
            'location-longitude' => array('2.3488000', ''),
            'location' => array('Paris', ''),
            'location-start-date' => array('2014-01-05', ''),
            'location-end-date' => array('2014-01-07', ''),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertEquals(0, count($errors));
        $this->assertEquals(1, count($tripInfo['locations']));
    }

    public function testEmptyLocationsAfterEmptyRowRemoved()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array (''),
            'location-latitude' => array(''),
            'location-longitude' => array( ''),
            'location' => array(''),
            'location-start-date' => array(''),
            'location-end-date' => array(''),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorNoLocationSpecified', $errors);
        $this->assertEquals(1, count($tripInfo['locations']));
    }

    public function testEndDateBeforeStartDateGetsFixed()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', '2643743'),
            'location-latitude' => array('48.8534100', '51.5085300'),
            'location-longitude' => array('2.3488000', '-0.1257400'),
            'location' => array('Paris', 'London'),
            'location-start-date' => array('2014-01-07', '2014-01-08'),
            'location-end-date' => array('2014-01-05', '2014-01-09'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertEquals(0, count($errors));
        $this->assertEquals(strtotime('2014-01-05'), $tripInfo['locations'][0]->startDate);
        $this->assertEquals(strtotime('2014-01-07'), $tripInfo['locations'][0]->endDate);
        $this->assertEquals(strtotime('2014-01-08'), $tripInfo['locations'][1]->startDate);
        $this->assertEquals(strtotime('2014-01-09'), $tripInfo['locations'][1]->endDate);
    }

    public function testEmptyRowTwoTripsOverlappingDates()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', '2643743', ''),
            'location-latitude' => array('48.8534100', '51.5085300', ''),
            'location-longitude' => array('2.3488000', '-0.1257400', ''),
            'location' => array('Paris', 'London', ''),
            'location-start-date' => array('2014-01-05', '2014-01-06', ''),
            'location-end-date' => array('2014-01-07', '2014-01-06', ''),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorOverlappingDates', $errors);
        $this->assertEquals(3, count($tripInfo['locations']));
    }

    public function testFourTripsOverlappingDates()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', '2643743', '2618425', '3143244'),
            'location-latitude' => array('48.8534100', '51.5085300', '55.6759400', '59.9127300'),
            'location-longitude' => array('2.3488000', '-0.1257400', '12.5655300', '10.7460900'),
            'location' => array('Paris', 'London', 'Copenhagen', 'Oslo'),
            'location-start-date' => array('2014-01-05', '2014-01-06', '2014-01-10', '2014-01-09'),
            'location-end-date' => array('2014-01-07', '2014-01-06', '2014-01-10', '2014-01-15'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertContains('TripErrorOverlappingDates', $errors);
        $this->assertEquals(5, count($tripInfo['locations']));
    }

    public function testFourTripsNoOverlappingDatesOrdered()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', '2643743', '2618425', '3143244'),
            'location-latitude' => array('48.8534100', '51.5085300', '55.6759400', '59.9127300'),
            'location-longitude' => array('2.3488000', '-0.1257400', '12.5655300', '10.7460900'),
            'location' => array('Paris', 'London', 'Copenhagen', 'Oslo'),
            'location-start-date' => array('2014-01-05', '2014-01-07', '2014-01-10', '2014-01-10'),
            'location-end-date' => array('2014-01-07', '2014-01-08', '2014-01-10', '2014-01-15'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertEquals(0, count($errors));
        $this->assertEquals(4, count($tripInfo['locations']));
    }

    public function testFourTripsNoOverlappingDatesUnOrdered()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', '2643743', '2618425', '3143244'),
            'location-latitude' => array('48.8534100', '51.5085300', '55.6759400', '59.9127300'),
            'location-longitude' => array('2.3488000', '-0.1257400', '12.5655300', '10.7460900'),
            'location' => array('Paris', 'London', 'Copenhagen', 'Oslo'),
            'location-start-date' => array('2014-01-05', '2014-01-10', '2014-01-10', '2014-01-07'),
            'location-end-date' => array('2014-01-07', '2014-01-10', '2014-01-15', '2014-01-08'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertEquals(0, count($errors));
        $this->assertEquals(4, count($tripInfo['locations']));
    }

    public function testDatesOrderedOrderingPersisted()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', '2643743', '2618425', '3143244'),
            'location-latitude' => array('48.8534100', '51.5085300', '55.6759400', '59.9127300'),
            'location-longitude' => array('2.3488000', '-0.1257400', '12.5655300', '10.7460900'),
            'location' => array('Paris', 'London', 'Copenhagen', 'Oslo'),
            'location-start-date' => array('2014-01-05', '2014-01-07', '2014-01-10', '2014-01-10'),
            'location-end-date' => array('2014-01-07', '2014-01-08', '2014-01-10', '2014-01-15'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertEquals(0, count($errors));
        $this->assertEquals(4, count($tripInfo['locations']));
        $this->assertEquals(2988507, $tripInfo['locations'][0]->geonameId);
        $this->assertEquals(2643743, $tripInfo['locations'][1]->geonameId);
        $this->assertEquals(2618425, $tripInfo['locations'][2]->geonameId);
        $this->assertEquals(3143244, $tripInfo['locations'][3]->geonameId);
    }

    public function testDatesUnOrderedGetOrdered()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', '2643743', '2618425', '3143244'),
            'location-latitude' => array('48.8534100', '51.5085300', '55.6759400', '59.9127300'),
            'location-longitude' => array('2.3488000', '-0.1257400', '12.5655300', '10.7460900'),
            'location' => array('Paris', 'London', 'Copenhagen', 'Oslo'),
            'location-start-date' => array('2014-01-05', '2014-01-10', '2014-01-10', '2014-01-07'),
            'location-end-date' => array('2014-01-07', '2014-01-10', '2014-01-15', '2014-01-08'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);

        $this->assertEquals(0, count($errors));
        $this->assertEquals(4, count($tripInfo['locations']));
        $this->assertEquals(2988507, $tripInfo['locations'][0]->geonameId);
        $this->assertEquals(3143244, $tripInfo['locations'][1]->geonameId);
        $this->assertEquals(2643743, $tripInfo['locations'][2]->geonameId);
        $this->assertEquals(2618425, $tripInfo['locations'][3]->geonameId);
    }

    public function testCreateTrip()
    {
        $vars = array(
            'trip-title' => 'Trip Name',
            'trip-description' => 'Trip Description',
            'location-geoname-id' => array ('2988507', '2643743', '2618425', '3143244'),
            'location-latitude' => array('48.8534100', '51.5085300', '55.6759400', '59.9127300'),
            'location-longitude' => array('2.3488000', '-0.1257400', '12.5655300', '10.7460900'),
            'location' => array('Paris', 'London', 'Copenhagen', 'Oslo'),
            'location-start-date' => array('2014-01-05', '2014-01-07', '2014-01-10', '2014-01-10'),
            'location-end-date' => array('2014-01-07', '2014-01-08', '2014-01-10', '2014-01-15'),
        );
        $tripsModel = new TripsModel();

        list($errors, $tripInfo) = $tripsModel->checkCreateEditVars($vars);
        $this->assertEquals(0, count($errors));

        $member = new Member();
        $member->findById(1223);
        $errors = $tripsModel->createTrip($tripInfo, $member);

        $this->assertEquals(0, count($errors));
    }

}
