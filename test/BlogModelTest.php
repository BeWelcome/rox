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

        $this->assertTrue(is_numeric($model->countRecentPosts()));
        $this->assertTrue(is_numeric($model->countRecentPosts(74)));
        $this->assertTrue(is_numeric($model->countRecentPosts('fake51')));
        $this->assertTrue($model->countRecentPosts('fake51') == 0);
    }

    public function testGetRecentPostsArray()
    {
        $model = new Blog;

        $this->assertTrue(is_array($model->getRecentPostsArray()));
        $this->assertTrue(is_array($model->getRecentPostsArray(74)));
        $this->assertTrue(is_array($model->getRecentPostsArray('fake51')));
        $this->assertTrue(count($model->getRecentPostsArray('fake51')) == 0);
        $this->assertTrue(is_array($model->getRecentPostsArray(false, false, 2)));
        $this->assertTrue(is_array($model->getRecentPostsArray(74, false, 2)));
    }

    public function testGetMemberByUsername()
    {
        $model = new Blog;

        $member = $model->getMemberByUsername('fake51');
        $this->assertTrue(is_object($member));
        $this->assertTrue($member instanceof Member);
        $this->assertTrue($member->isLoaded());
    }

    public function testGetCategoryArray()
    {
        $model = new Blog;

        $this->assertTrue(is_array($model->getCategoryArray()));
        $this->assertTrue(is_array($model->getRecentPostsArray(11)));
        $this->assertTrue(is_array($model->getRecentPostsArray('fake51')));
        $this->assertTrue(count($model->getRecentPostsArray('fake51')) == 0);

        $member = $model->getMemberByUsername('fake51');
        $this->assertTrue(is_array($model->getRecentPostsArray(false, $member)));
        $this->assertTrue(is_array($model->getRecentPostsArray(11, $member)));
    }

    public function testSearchPosts()
    {
        $model = new Blog;

        $this->assertTrue(is_array($model->searchPosts('')));
        $this->assertTrue(is_array($model->searchPosts('henri')));
        $this->assertTrue(count($model->searchPosts('henri')) > 0);
    }

    public function testGetTripFromUserIt()
    {

    }

    public function testIsUserTrip()
    {

    }
}
