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
     * @subpackage EntityTests
     * @author     Fake51
     * @copyright  2009 BeVolunteer
     * @license    http://www.gnu.org/licenses/gpl-2.0.html GPL 2
     * @link       http://www.bewelcome.org
     */

    /**
     * tests the Geo entity class
     *
     * @package    Tests
     * @subpackage EntityTests
     * @author     Fake51
     */

require_once("PHPUnit/Framework.php");

class GeoEntityTest extends PHPUnit_Framework_TestCase
{
    protected $entity_factory;

    public function setUp()
    {
        require_once 'core_includes.php';
        $this->entity_factory = new RoxEntityFactory;
    }

    public function testCreation()
    {
        $geo = $this->newGeo();
        $this->assertEquals(true, $geo instanceof Geo);
        $this->assertEquals(true, $geo instanceof RoxEntityBase);
    }

    public function testGetParent1()
    {
        $geo = $this->newGeo();
        $this->assertFalse($geo->getParent());
    }

    public function testGetAncestorLine1()
    {
        $geo = $this->newGeo();
        $result = $geo->getAncestorLine();
        $this->assertTrue(is_array($result));
        $this->assertTrue(empty($result));
    }

    public function testGetCountry1()
    {
        $geo = $this->newGeo();
        $this->assertFalse($geo->getCountry());
    }

    public function testGetChildren1()
    {
        $geo = $this->newGeo();
        $result = $geo->getChildren();
        $this->assertTrue(is_array($result));
        $this->assertTrue(empty($result));
    }

    public function testGetAllParents1()
    {
        $geo = $this->newGeo();
        $result = $geo->getAllParents();
        $this->assertTrue(is_array($result));
        $this->assertTrue(empty($result));
    }

    public function testGetAlternateName1()
    {
        $geo = $this->newGeo();
        $this->assertTrue(is_string($geo->getAlternateName('en')));
        $this->assertTrue($geo->getAlternateName('en') === '');
    }

    public function testGetName1()
    {
        $geo = $this->newGeo();
        $this->assertTrue(is_string($geo->getName()));
        $this->assertTrue($geo->getName() === '');
    }

    public function testGetAllAlternateNames1()
    {
        $geo = $this->newGeo();
        $result = $geo->getAllAlternateNames();
        $this->assertTrue(is_array($result));
        $this->assertTrue(empty($result));
    }

    public function testGetUsageForAllTypes1()
    {
        $geo = $this->newGeo();
        $result = $geo->getUsageForAllTypes();
        $this->assertTrue(is_array($result));
        $this->assertTrue(empty($result));
    }

    public function testGetTotalUsage1()
    {
        $geo = $this->newGeo();
        $result = $geo->getTotalUsage();
        $this->assertTrue(is_int($result));
        $this->assertTrue($result === 0);
    }

    public function testPlaceType1()
    {
        $geo = $this->newGeo();
        $result = $geo->placeType();
        $this->assertTrue(is_string($result));
        $this->assertTrue($result === '');
    }

    public function testIsCity1()
    {
        $geo = $this->newGeo();
        $this->assertFalse($geo->isCity());
    }

    public function testIsCountry1()
    {
        $geo = $this->newGeo();
        $this->assertFalse($geo->isCountry());
    }

    public function testIsRegion1()
    {
        $geo = $this->newGeo();
        $this->assertFalse($geo->isRegion());
    }

    public function testFindLocationByCoordinates1()
    {
        $geo = $this->newGeo();
        $this->setExpectedException('Exception');
        $geo->findLocationsByCoordinates(array());
    }

    public function testFindLocationByCoordinates2()
    {
        $geo = $this->newGeo();
        $this->setExpectedException('Exception');
        $geo->findLocationsByCoordinates(array('lat' => ''));
    }

    public function testFindLocationByCoordinates3()
    {
        $geo = $this->newGeo();
        $this->setExpectedException('Exception');
        $geo->findLocationsByCoordinates(array('long' => ''));
    }

    public function testFindLocationByCoordinates4()
    {
        $geo = $this->newGeo();
        $this->setExpectedException('Exception');
        $geo->findLocationsByCoordinates(array('long' => '', 'lat' => ''));
    }

    public function testFindLocationByCoordinates5()
    {
        $geo = $this->newGeo();
        $this->setExpectedException('Exception');
        $geo->findLocationsByCoordinates(array('long' => 1, 'lat' => ''));
    }

    public function testFindLocationByCoordinates6()
    {
        $geo = $this->newGeo();
        $this->setExpectedException('Exception');
        $geo->findLocationsByCoordinates(array('long' => '', 'lat' => 1));
    }

    public function testFindLocationByCoordinates7()
    {
        $geo = $this->newGeo();
        $result = $geo->findLocationsByCoordinates(array('long' => 1, 'lat' => 1));
        $this->assertTrue(is_array($result));
        $this->assertTrue(empty($result));
    }

    public function testFindLocationByCoordinates8()
    {
        $geo = $this->newGeo();
        $result = $geo->findLocationsByCoordinates(array('long' => 13.4, 'lat' => 52.51));
        $this->assertTrue(is_array($result));
        $this->assertTrue(!empty($result));
        $test = false;
        foreach ($result as $geo)
        {
            $this->assertTrue($geo->isLoaded());
            $this->assertTrue($geo instanceof Geo);
            if ($geo->getName() == 'Berlin') $test = true;
        }
        $this->assertTrue($test);
    }

    public function testFindLocationByCoordinates9()
    {
        $geo = $this->newGeo();
        $result = $geo->findLocationsByCoordinates(array('long' => 13.27, 'lat' => 52.51));
        $this->assertTrue(is_array($result));
        $test = false;
        foreach ($result as $geo)
        {
            $this->assertTrue($geo->isLoaded());
            $this->assertTrue($geo instanceof Geo);
            if ($geo->getName() == 'Berlin') $test = true;
        }
        $this->assertFalse($test);
    }

    public function testFindLocationByCoordinates10()
    {
        $geo = $this->newGeo();
        $result = $geo->findLocationsByCoordinates(array('long' => 13.4, 'lat' => 52.37));
        $this->assertTrue(is_array($result));
        $test = false;
        foreach ($result as $geo)
        {
            $this->assertTrue($geo->isLoaded());
            $this->assertTrue($geo instanceof Geo);
            if ($geo->getName() == 'Berlin') $test = true;
        }
        $this->assertFalse($test);
    }

    public function testFindLocationsByName1()
    {
        $geo = $this->newGeo();
        $result = $geo->findLocationsByName('');
        $this->assertTrue(is_array($result));
        $this->assertTrue(empty($result));
    }

    public function testFindLocationsByName2()
    {
        $geo = $this->newGeo();
        $result = $geo->findLocationsByName('Berlin');
        $this->assertTrue(is_array($result));
        $this->assertTrue(!empty($result));
        $test = false;
        foreach ($result as $geo)
        {
            $this->assertTrue($geo->isLoaded());
            if ($geo->getName() == 'Berlin') $test = true;
        }
        $this->assertTrue($test);
    }

    public function testFindLocationsByName3()
    {
        $geo = $this->newGeo();
        $result = $geo->findLocationsByName('blahblahblah');
        $this->assertTrue(is_array($result));
        $this->assertTrue(empty($result));
    }

    private function newGeo()
    {
        return $this->entity_factory->create('Geo');
    }
}
