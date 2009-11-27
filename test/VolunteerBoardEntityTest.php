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
     * tests the VolunteerBoard entity class
     *
     * @package    Tests
     * @subpackage EntityTests
     * @author     Fake51
     */

require_once 'PHPUnit/Framework.php';

require_once 'core_includes.php';

class VolunteerBoardEntityTest extends PHPUnit_Framework_TestCase
{
    protected $entity_factory;

    public function setup()
    {
        $this->entity_factory = new RoxEntityFactory;
    }

    public function testEntity()
    {
        $m = $this->entity_factory->create('VolunteerBoard');
        $this->assertTrue($m instanceof VolunteerBoard);
        $this->assertTrue($m instanceof RoxEntityBase);
    }

    public function testFindById1()
    {
        $m = $this->entity_factory->create('VolunteerBoard');
        $this->assertFalse($m->isLoaded());
        $result = $m->findById(1);
        $this->assertTrue($m->isLoaded());
        $this->assertTrue($result instanceof VolunteerBoard);
    }

    public function testFindById2()
    {
        $m = $this->entity_factory->create('VolunteerBoard');
        $this->assertFalse($m->isLoaded());
        $result = $m->findById(1000);
        $this->assertFalse($m->isLoaded());
        $this->assertFalse($result);
    }

    public function testFindByName1()
    {
        $m = $this->entity_factory->create('VolunteerBoard');
        $this->assertFalse($m->isLoaded());
        $result = $m->findByName('Accepters_board');
        $this->assertTrue($m->isLoaded());
        $this->assertTrue($result instanceof VolunteerBoard);
    }

    public function testFindByName2()
    {
        $m = $this->entity_factory->create('VolunteerBoard');
        $this->assertFalse($m->isLoaded());
        $result = $m->findByName('blah');
        $this->assertFalse($m->isLoaded());
        $this->assertFalse($result);
    }

    public function testConstructor1()
    {
        $m = $this->entity_factory->create('VolunteerBoard', 1);
        $this->assertTrue($m->isLoaded());
    }

    public function testConstructor2()
    {
        $m = $this->entity_factory->create('VolunteerBoard', 100);
        $this->assertFalse($m->isLoaded());
    }
}
