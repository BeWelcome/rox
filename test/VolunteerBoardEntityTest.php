<?php

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
