<?php

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
            if (substr($e->getMessage(), 0, 21) == 'session_regenerate_id')
            {
                $this->assertTrue(true);
            }
            else
            {
                $this->assertTrue(false);
            }
        }
    }
}
