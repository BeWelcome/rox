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
     * tests the Member entity class
     *
     * @package    Tests
     * @subpackage EntityTests
     * @author     Fake51
     */

require_once 'PHPUnit/Framework.php';

require_once 'core_includes.php';

class MemberEntityTest extends PHPUnit_Framework_TestCase
{
    protected $entity_factory;

    public function setup()
    {
        $this->entity_factory = new RoxEntityFactory;
    }

    public function testEntity()
    {
        $m = $this->entity_factory->create('Member');
        $this->assertTrue($m instanceof Member);
        $this->assertTrue($m instanceof RoxEntityBase);
    }

    public function testIsActive1()
    {
        $m = $this->entity_factory->create('Member');
        $this->assertFalse($m->isActive());
    }

    public function testLoad()
    {
        $m = $this->entity_factory->create('Member');
        $this->assertFalse($m->isActive());
        $m->findById(74);
        $this->assertTrue($m->isActive());

        $m = $this->entity_factory->create('Member', 74);
        $this->assertTrue($m->isActive());
    }

    public function testGetOldRights()
    {
        $m = $this->entity_factory->create('Member');
        $array = $m->getOldRights();
        $this->assertTrue(is_array($array));
        $this->assertTrue(empty($array));
        $m->findById(1);
        $array = $m->getOldRights();
        $this->assertTrue(is_array($array));
        $this->assertFalse(empty($array));
    }

    public function testLogOut()
    {
        $m = $this->entity_factory->create('Member');
        $this->assertFalse($m->logOut());
        $m->findById(1);
        // this is very hackish. However, the way the tests are done, the framework
        // sends headers as it feels like. Hence, session_regenerate_id will fall over
        // testing for that indicates success, actually (in some strange far away place)
        try
        {
            $this->assertTrue($m->logOut());
        }
        catch (Exception $e)
        {
            $this->assertTrue(substr($e->getMessage(), 0, 21) == 'session_regenerate_id');
        }
    }

    public function testHasOldRight1()
    {
        $m = $this->entity_factory->create('Member')->findByUsername('Admin');
        $this->assertTrue($m->isLoaded());
        $this->assertTrue($m->hasOldRight(array('Admin' => '')));
    }

    public function testHasOldRight2()
    {
        $m = $this->entity_factory->create('Member')->findByUsername('Admin');
        $this->assertTrue($m->isLoaded());
        $this->assertFalse($m->hasOldRight(array('blahblah' => '')));
    }

    public function testHasOldRight3()
    {
        $m = $this->entity_factory->create('Member')->findByUsername('Fake51');
        $this->assertTrue($m->isLoaded());
        $this->assertFalse($m->hasOldRight(array('Admin' => '')));
    }

    public function testInactivateProfile1()
    {
        $m = $this->entity_factory->create('Member');
        $this->assertFalse($m->inactivateProfile());
    }

    public function testinactivateProfile2()
    {
        $m = $this->entity_factory->create('Member')->findByUsername('Fake51');
        $this->assertNotEquals('ChoiceInactive', $m->Status);
        $this->assertTrue($m->inactivateProfile());
        $this->assertEquals('ChoiceInactive', $m->Status);
    }

    public function testActivateProfile1()
    {
        $m = $this->entity_factory->create('Member');
        $this->assertFalse($m->activateProfile());
    }

    public function testActivateProfile2()
    {
        $m = $this->entity_factory->create('Member')->findByUsername('Fake51');
        $this->assertEquals('ChoiceInactive', $m->Status);
        $this->assertTrue($m->activateProfile());
        $this->assertEquals('Active', $m->Status);
    }

    public function testActivateProfile3()
    {
        $m = $this->entity_factory->create('Member')->findByUsername('Fake51');
        $m->Status = 'TakenOut';
        $this->assertFalse($m->activateProfile());
        $m->Status = 'Banned';
        $this->assertFalse($m->activateProfile());
        $m->Status = 'SuspendedBeta';
        $this->assertFalse($m->activateProfile());
        $m->Status = 'AskToLeave';
        $this->assertFalse($m->activateProfile());
        $m->Status = 'PassedAway';
        $this->assertFalse($m->activateProfile());
        $m->Status = 'Buggy';
        $this->assertFalse($m->activateProfile());
    }

    public function testRemoveProfile1()
    {
        $m = $this->entity_factory->create('Member');
        $this->assertFalse($m->activateProfile());
    }

    public function testRemoveProfile2()
    {
        $m = $this->entity_factory->create('Member')->findByUsername('Fake51');
        $this->assertNotEquals('AskToLeave', $m->Status);
        $this->assertTrue($m->removeProfile());
        $this->assertEquals('AskToLeave', $m->Status);
        $m->Status = 'Active';
        $m->update();
    }

}
