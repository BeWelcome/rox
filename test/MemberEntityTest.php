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
}
