<?php

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class ForumsModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new Forums;
        $this->assertTrue($model instanceof Forums);
        $this->assertTrue($model instanceof RoxModelBase);
    }

    public function testSearchUserPosts()
    {
        $model = new Forums;
        $this->assertTrue(is_array($model->searchUserposts(0)));
        $this->assertTrue(count($model->searchUserposts(0)) == 0);
        try
        {
            $model->searchUserposts('fasfeaciaeffasxfsefea');
            $this->assertFalse(true, 'Forums::searchUserPosts() did not throw an exception on bad user id');
        }
        catch (Exception $e)
        {
            $this->assertTrue(get_class($e) == 'PException');
        }
        $this->assertTrue(is_array($model->searchUserposts('henri')));
        $this->assertTrue(count($model->searchUserposts('henri')) != 0);

    }

}
