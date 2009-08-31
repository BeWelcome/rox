<?php

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class TripModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new Trip;
        $this->assertTrue($model instanceof Trip);
        $this->assertTrue($model instanceof RoxModelBase);
    }

    public function testGetBlogGeo()
    {
        $model = new Trip;
        $this->assertFalse($model->getBlogGeo(0));
        $this->assertTrue($model->getBlogGeo(778) instanceof Geo);
    }

    public function testGetTripsForUser()
    {
        $model = new Trip;
        $trips = $model->getTripsForUser(0);
        $this->assertTrue(is_array($trips));
        $this->assertTrue(empty($trips));
        $trips = $model->getTripsForUser(1);
        $this->assertTrue(is_array($trips));
        $this->assertFalse(empty($trips));
    }

    public function testCreateTrip()
    {
        $model = new Trip;

        /* todo: find out how to make these work
        $this->assertFalse($model->createTrip(false, false));
        $this->assertFalse($model->createTrip(array(1,1), false));
        $this->assertFalse($model->createTrip(array('n' => 'test trip', 'd' => 'test description'), false));
        */

        $members = new MembersModel;
        $member = $members->getMemberWithUsername('henri');
        $this->assertFalse($model->createTrip(false, $member));
        $trip_id = $model->createTrip(array('n' => 'test trip', 'd' => 'test description'), $member);
        $this->assertTrue($trip_id > 0);
    }

    public function testInsertTrip()
    {
        $model = new Trip;

        $this->assertFalse($model->insertTrip('new trip', 'new test trip', 0));
        $this->assertTrue($model->insertTrip('new trip', 'new test trip', 1) > 0);
    }

    public function testGetTrips()
    {
        $model = new Trip;

        $this->assertTrue($model->getTrips() instanceof PDBStatement);
        $this->assertTrue($model->getTrips(0) instanceof PDBStatement);
        $this->assertTrue($model->getTrips(1) instanceof PDBStatement);
        $this->assertTrue($model->getTrips('admin') instanceof PDBStatement);
    }

    public function testGetTripData()
    {
        $model = new Trip;

        $tripdata = $model->getTripData();
        $this->assertTrue(is_array($tripdata));
        $this->assertTrue(empty($tripdata));

        
        $this->assertTrue($trips = $model->getTrips() instanceof PDBStatement);

        $tripdata = $model->getTripData();
        $this->assertTrue(is_array($tripdata));
        $this->assertFalse(empty($tripdata));
    }

    public function testGetTrip()
    {
        $model = new Trip;

        $this->assertFalse($model->getTrip(0));
        $trip = $model->getTrip(1);
        $this->assertTrue($trip instanceof StdClass);
        $this->assertTrue($trip->trip_id == 1);
    }

    public function testCheckTripItemOwnerShip()
    {
        $model = new Trip;

        $this->assertFalse($model->checkTripItemOwnerShip());
        $this->assertTrue($model->checkTripItemOwnerShip(array(1,2)));
    }

    public function getTripsForLocation()
    {

    }
}
