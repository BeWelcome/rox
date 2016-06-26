<?php

namespace Rox\CommunityNews\Model;

use Illuminate\Database\Eloquent\Collection;
use PHPUnit_Framework_TestCase;
use Rox\Core\Exception\InvalidArgumentException;
use Rox\Core\Exception\NotFoundException;
use Rox\Core\Factory\DatabaseFactory;
use Rox\Core\Kernel\Application;

class CommunityNewsRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $databaseFactory = new DatabaseFactory();
        $databaseFactory->__invoke();
    }

    public function tearDown()
    {
    }

    public function testGetLatestZero()
    {
        $this->expectException(InvalidArgumentException::class);

        $model = new CommunityNews();
        $model->getLatest(0);
    }

    public function testGetLatestNegative()
    {
        $this->expectException(InvalidArgumentException::class);

        $model = new CommunityNews();
        $model->getLatest(-1);
    }

    public function testGetLatestSingle()
    {
        $model = new CommunityNews();
        $communityNews = $model->getLatest();

        $this->assertEquals(CommunityNews::class, get_class($communityNews));
    }

    public function testGetLatestMultiple()
    {
        $model = new CommunityNews();
        $communityNews = $model->getLatest(2);

        $this->assertEquals(Collection::class, get_class($communityNews));;
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
        $model->getById(-1);
    }
}
