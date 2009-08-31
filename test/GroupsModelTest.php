<?php

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class GroupsModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new GroupsModel;
        $this->assertTrue($model instanceof GroupsModel);
        $this->assertTrue($model instanceof RoxModelBase);
    }

    public function testFindGroup()
    {
        $model = new GroupsModel;
        $this->assertFalse($model->findGroup(0));
        $this->assertFalse($model->findGroup('this'));
        $this->assertTrue($model->findGroup(17) instanceof Group);
    }

    public function testFindMembersByName()
    {
        $model = new GroupsModel;
        $result = $model->findMembersByName(false, false);
        $this->assertTrue(is_array($result));
        $this->assertTrue(empty($result));
        $result = $model->findMembersByName(false, 'admin');
        $this->assertTrue(is_array($result));
        $this->assertFalse(empty($result));

        $group = $model->findGroup(17);
        $this->assertTrue($group instanceof Group);

        $result = $model->findMembersByName($group, 'admin');
        $this->assertTrue(is_array($result));
        $this->assertFalse(empty($result));
    }
}
