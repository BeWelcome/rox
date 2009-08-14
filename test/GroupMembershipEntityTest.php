<?php

require_once("PHPUnit/Framework.php");

class GroupMembershipEntityTest extends PHPUnit_Framework_TestCase
{
    protected $entity_factory;

    protected $group_id;

    public function setUp()
    {
        require_once 'core_includes.php';
        $this->entity_factory = new RoxEntityFactory;
    }

    public function testEntity()
    {
        $mg = $this->entity_factory->create('GroupMembership');
        $this->assertTrue($mg instanceof GroupMembership);
        $this->assertTrue($mg instanceof RoxEntityBase);
    }
    
    public function testTablename()
    {
        $m = $this->entity_factory->create('GroupMembership');
        $this->assertTrue($m->getTableName() != '');
    }

    public function testGetGroupMembership1()
    {
        $member = $this->entity_factory->create('Member');
        $group = $this->entity_factory->create('Group');
        $this->assertFalse($this->entity_factory->create('GroupMembership')->getMemberShip($group, $member));

        $member = $this->entity_factory->create('Member')->findById(2);
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($group instanceof Group);
        $this->assertFalse($this->entity_factory->create('Group')->getMemberShip($group, $member));
    }

    public function testGetGroupMembers()
    {
        $group = $this->entity_factory->create('Group');
        $this->assertTrue(is_array($this->entity_factory->create('GroupMembership')->getGroupMembers($group)));

        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($group instanceof Group);
        $this->assertTrue(is_array($this->entity_factory->create('GroupMembership')->getGroupMembers($group)));
    }

    public function testGetNewGroupMembers()
    {
        $group = $this->entity_factory->create('Group');
        $this->assertTrue(is_array($this->entity_factory->create('GroupMembership')->getNewGroupMembers($group)));

        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($group instanceof Group);
        $this->assertTrue(is_array($this->entity_factory->create('GroupMembership')->getNewGroupMembers($group)));
    }

    public function testMemberJoin()
    {
        $member = $this->entity_factory->create('Member')->findById(2);
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($group instanceof Group);
        $this->assertFalse($this->entity_factory->create('Group')->getMemberShip($group, $member));

        $this->assertTrue($this->entity_factory->create('GroupMembership')->memberJoin($group, $member));
    }

    public function testGetGroupMembership2()
    {
        $member = $this->entity_factory->create('Member');
        $group = $this->entity_factory->create('Group');
        $this->assertFalse($this->entity_factory->create('GroupMembership')->getMemberShip($group, $member));

        $member = $this->entity_factory->create('Member')->findById(2);
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($group instanceof Group);
        $this->assertFalse($this->entity_factory->create('Group')->getMemberShip($group, $member));
    }

    public function testIsMember1()
    {
        $member = $this->entity_factory->create('Member');
        $group = $this->entity_factory->create('Group');
        $this->assertTrue($member instanceof Member);
        $mg = $this->entity_factory->create('GroupMembership');
        $this->assertFalse($mg->isMember($group, $member));

        $member = $this->entity_factory->create('Member');
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($group instanceof Group);
        $mg = $this->entity_factory->create('GroupMembership');
        $this->assertFalse($mg->isMember($group, $member));

        $member = $this->entity_factory->create('Member')->findById(2);
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($group instanceof Group);

        $this->assertTrue($this->entity_factory->create('GroupMembership')->isMember($group, $member));
    }

    public function testGetMemberGroups()
    {
        $member = $this->entity_factory->create('Member');
        $this->assertTrue($member instanceof Member);
        $mg = $this->entity_factory->create('GroupMembership');
        $this->assertTrue(is_array($mg->getMemberGroups($member)));

        $member = $this->entity_factory->create('Member')->findById(2);
        $this->assertTrue($member instanceof Member);
        $mg = $this->entity_factory->create('GroupMembership');
        $this->assertTrue(is_array($mg->getMemberGroups($member)));

        $member = $this->entity_factory->create('Member')->findById(74);
        $this->assertTrue($member instanceof Member);
        $mg = $this->entity_factory->create('GroupMembership');
        $this->assertTrue(is_array($mg->getMemberGroups($member)));
    }

    public function testUpdateStatus()
    {
        $mg = $this->entity_factory->create('GroupMembership');
        $this->assertFalse($mg->updateStatus($mg));

        $member = $this->entity_factory->create('Member')->findById(2);
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($group instanceof Group);

        $mg = $this->entity_factory->create('GroupMembership')->getMembership($group, $member);
        $this->assertTrue($mg instanceof GroupMembership);

        $this->assertFalse($this->entity_factory->create('GroupMembership')->updateStatus(''));
        $this->assertFalse($this->entity_factory->create('GroupMembership')->updateStatus('Blah'));
        $this->assertFalse($this->entity_factory->create('GroupMembership')->updateStatus('Invited'));
        $this->assertFalse($this->entity_factory->create('GroupMembership')->updateStatus('In'));
    }

    public function testMemberLeave()
    {
        $member = $this->entity_factory->create('Member')->findById(2);
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($group instanceof Group);

        $this->assertTrue($this->entity_factory->create('GroupMembership')->memberLeave($group, $member));
    }

    public function testIsMember2()
    {
        $member = $this->entity_factory->create('Member');
        $group = $this->entity_factory->create('Group');
        $mg = $this->entity_factory->create('GroupMembership');
        $this->assertFalse($mg->isMember($group, $member));

        $member = $this->entity_factory->create('Member');
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($group instanceof Group);
        $mg = $this->entity_factory->create('GroupMembership');
        $this->assertFalse($mg->isMember($group, $member));

        $member = $this->entity_factory->create('Member')->findById(2);
        $group = $this->entity_factory->create('Group')->findById(17);
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($group instanceof Group);

        $this->assertFalse($this->entity_factory->create('GroupMembership')->isMember($group, $member));
    }

}
