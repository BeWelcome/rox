<?php
/*
Copyright (c) 2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.
*/

    /**
     * @package    Tests
     * @subpackage ModelTests
     * @author     Fake51
     * @copyright  2009 BeVolunteer
     * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL 2
     * @link       http://www.bewelcome.org
     */

    /**
     * tests the Trip model class
     *
     * @package    Tests
     * @subpackage ModelTests
     * @author     Fake51
     */

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
        $this->assertFalse($model->checkTripItemOwnerShip(array(1,2)));
    }

    public function getTripsForLocation()
    {

    }
}
