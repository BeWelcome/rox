<?php

namespace Rox\Start\Controller;

use Doctrine\Common\Cache\Cache;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use Rox\Start\Service\StartService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;

class StartControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var StartController
     */
    protected $controller;

    /**
     * @var StartService|PHPUnit_Framework_MockObject_MockObject
     */
    protected $startService;

    /**
     * @var Cache|PHPUnit_Framework_MockObject_MockObject
     */
    protected $cache;

    /**
     * @var EngineInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $engine;

    public function setUp()
    {
        $this->startService = $this->getMockBuilder(StartService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->cache = $this->createMock(Cache::class);

        $this->engine = $this->createMock(EngineInterface::class);

        $this->controller = new StartController($this->startService, $this->cache);

        $this->controller->setEngine($this->engine);
    }

    public function testInvoke()
    {
        $this->cache->expects($this->once())->method('fetch');

        $this->startService->expects($this->once())->method('getStatistics');

        $this->cache->expects($this->once())->method('save');

        $this->engine->expects($this->once())->method('render');

        $result = $this->controller->startPageAction();

        $this->assertInstanceOf(Response::class, $result);
    }
}
