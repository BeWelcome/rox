<?php

require_once("PHPUnit/Framework.php");

class GroupEntityTest extends PHPUnit_Framework_TestCase
{
    protected $entity_factory;

    protected $group_id;

    public function setUp()
    {
        require_once 'core_includes.php';
        $this->entity_factory = new RoxEntityFactory;
    }

    public function testCreation()
    {
        $group = $this->entity_factory->create('Group');
        $this->assertEquals(true, $group instanceof Group);
        $this->assertEquals(true, $group instanceof RoxEntityBase);
    }


    public function testFindGroupGood()
    {
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertEquals(true, $group instanceof RoxEntityBase);
    }

    public function testFindGroupBad()
    {
        $group = $this->entity_factory->create('Group')->findById(1000000000);
        $this->assertEquals(false, $group);

        $group = $this->entity_factory->create('Group')->findById(-1);
        $this->assertEquals(false, $group);

        $group = $this->entity_factory->create('Group')->findById('here');
        $this->assertEquals(false, $group);
    }

    public function testLoaded()
    {
        $group = $this->entity_factory->create('Group');
        $this->assertEquals(true, $group instanceof Group);
        $this->assertEquals(false, $group->isLoaded());
        $new_group = $this->entity_factory->create('Group')->findById(17);
        $this->assertEquals(true, $group instanceof Group);
        $this->assertEquals(true, $new_group->isLoaded());
    }

    public function testGetMembers()
    {
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertEquals(true, $group instanceof Group);
        $members = $group->getMembers();
        $this->assertEquals(true, is_array($members));
        foreach ($members as $member)
        {
            $this->assertTrue($member instanceof Member);
            $this->assertTrue($member->isLoaded());
        }
    }

    public function testCreateGroup()
    {
        $group = $this->entity_factory->create('Group');
        $this->assertEquals(true, $group instanceof Group);
        $this->group_id = $group->createGroup(array('Group_' => 'phpunit test group' . date('Y-m-d'), 'Type' => 'Public'));
        $this->assertFalse(empty($this->group_id));
        $this->assertTrue($group->isLoaded());
    }

    public function testFindGroupByWhere()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
    }

    public function testGetEmailAcceptingMembers()
    {
        $group = $this->entity_factory->create('Group');
        $this->assertTrue($group instanceof Group);
        $this->assertTrue(is_array($group->getEmailAcceptingMembers()));
        $this->assertTrue(count($group->getEmailAcceptingMembers()) == 0);
        $new_group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($new_group instanceof Group);
        $this->assertTrue(is_array($new_group->getEmailAcceptingMembers()) && count($new_group->getEmailAcceptingMembers()) == 0);
    }


    public function testGetMembersEmpty()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $members = $group->getMembers();
        $this->assertTrue(is_array($members));
        $this->assertTrue(empty($members));
    }

    public function testGetMembersCount1()
    {
        $new_group = $this->entity_factory->create('Group');
        $this->assertTrue($new_group instanceof Group);
        $this->assertTrue($new_group->getMemberCount() === 0);
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $this->assertTrue($group->getMemberCount() === 0);
    }

    public function testMemberJoin()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $member = $this->entity_factory->create('Member')->findById(1);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($member->isLoaded());
        $this->assertTrue($group->memberJoin($member, 'In'));
    }

    public function testGetMembersCount2()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $this->assertTrue($group->getMemberCount() === 1);
    }

    public function testGetGroupOwner1()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $this->assertFalse($group->getGroupOwner());
    }

    public function testIsGroupOwner1()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $member = $this->entity_factory->create('Member')->findById(1);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($member->isLoaded());
        $this->assertFalse($group->isGroupOwner($member));

        $new_group = $this->entity_factory->create('Group');
        $this->assertFalse($new_group->isGroupOwner($member));
    }

    public function testSetGroupOwner()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $member = $this->entity_factory->create('Member')->findById(1);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($member->isLoaded());
        $this->assertTrue($group->setGroupOwner($member));
    }

    public function testIsGroupOwner2()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $this->assertTrue($group->isLoaded());
        $member = $this->entity_factory->create('Member')->findById(1);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($member->isLoaded());
        $this->assertTrue($group->isGroupOwner($member));
    }

    public function testGetGroupOwner2()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $member = $this->entity_factory->create('Member')->findById(1);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($member->isLoaded());
        $owner = $group->getGroupOwner();
        $this->assertTrue($owner instanceof Member);
        $this->assertTrue($owner->getPKValue() == $member->getPKValue());
    }

    public function testMemberLeave()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $member = $this->entity_factory->create('Member')->findById(1);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($member->isLoaded());
        $this->assertTrue($group->memberLeave($member));
    }

    public function testDeleteGroup()
    {
        $group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertTrue($group instanceof Group);
        $this->assertTrue($group->deleteGroup());
        $this->assertFalse($group->isLoaded());

        $old_group = $this->entity_factory->create('Group')->findByWhere("Name = 'phpunit test group" . date('Y-m-d') ."'");
        $this->assertFalse($old_group);

    }
}
