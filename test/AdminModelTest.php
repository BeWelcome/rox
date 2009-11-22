<?php

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class AdminModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new AdminModel;
        $this->assertTrue($model instanceof AdminModel);
        $this->assertTrue($model instanceof RoxModelBase);
    }

    public function testCountMembersWithStatus1()
    {
        $model = new AdminModel;
        $result = $model->countMembersWithStatus('blah');
        $this->assertTrue($result == 0);
    }

    public function testCountMembersWithStatus2()
    {
        $model = new AdminModel;
        $result = $model->countMembersWithStatus('Active');
        $this->assertTrue($result > 0);
    }


    public function testGetStatusOverview()
    {
        $model = new AdminModel;
        $result = $model->getStatusOverview();
        $this->assertTrue(is_array($result));
        foreach ($result as $status => $count)
        {
            $this->assertTrue(is_string($status));
            $this->assertTrue(is_numeric($count));
        }
    }

    public function testGetBadComments()
    {
        $model = new AdminModel;
        $result = $model->getBadComments();
        $this->assertTrue(is_array($result));
        foreach ($result as $comment)
        {
            $this->assertTrue($comment instanceof Comment);
            $this->assertTrue($comment->isLoaded());
        }
    }
}
