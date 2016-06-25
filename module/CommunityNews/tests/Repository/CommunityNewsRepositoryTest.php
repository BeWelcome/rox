<?php

namespace Rox\CommunityNews\Model;

use PHPUnit_Framework_TestCase;
use Rox\Core\Exception\NotFoundException;
use Rox\Core\Kernel\Application;

class CommunityNewsRepositoryTest extends PHPUnit_Framework_TestCase
{
    private $application = null;

    public function setUp()
    {
        $this->application = new Application('testing', false);
        $this->application->boot();
    }

    public function tearDown()
    {
        $this->application = null;
    }

    public function testGetLatest()
    {
        $model = new CommunityNews();
        $communityNews = $model->getLatest();

        $this->assertTrue(is_object($communityNews));
    }

    public function testGetAll()
    {
        $model = new CommunityNews();
        $communityNews = $model->getAll();

        $this->assertTrue(is_array($communityNews));
        $this->assertNotEmpty($communityNews);
        $this->assertGreaterThan(0, count($communityNews));
    }

    public function testGetById()
    {
        $model = new CommunityNews();
        $communityNews = $model->getById(1);

        $this->assertTrue(is_object($communityNews));
    }

    public function testGetByNonExistingId()
    {
        $this->expectException(NotFoundException::class);
        $model = new CommunityNews();
        $communityNews = $model->getById(-1);

        $this->assertTrue(is_object($communityNews));
    }
}
