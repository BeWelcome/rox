<?php

require_once 'PHPUnit/Framework.php';
require_once 'core_includes.php';

class BlogModelTest extends PHPUnit_Framework_TestCase
{

    public function testClass()
    {
        $model = new Blog;
        $this->assertTrue($model instanceof Blog);
        $this->assertTrue($model instanceof RoxModelBase);
    }

    public function testCountRecentPosts()
    {
        $model = new Blog;
        $this->assertTrue($model instanceof Blog);

        $this->assertTrue(is_numeric($model->countRecentPosts()));
        $this->assertTrue(is_numeric($model->countRecentPosts(74)));
        $this->assertTrue(is_numeric($model->countRecentPosts('fake51')));
        $this->assertTrue($model->countRecentPosts('fake51') == 0);
    }

    public function testGetRecentPostsArray()
    {
        $model = new Blog;
        $this->assertTrue($model instanceof Blog);

        $this->assertTrue(is_array($model->getRecentPostsArray()));
        $this->assertTrue(is_array($model->getRecentPostsArray(74)));
        $this->assertTrue(is_array($model->getRecentPostsArray('fake51')));
        $this->assertTrue(count($model->getRecentPostsArray('fake51')) == 0);
        $this->assertTrue(is_array($model->getRecentPostsArray(false, false, 2)));
        $this->assertTrue(is_array($model->getRecentPostsArray(74, false, 2)));
    }
}
