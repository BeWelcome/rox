<?php

namespace Rox\Start\Service;

use ArrayObject;
use Illuminate\Database\ConnectionInterface;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class StartServiceTest extends PHPUnit_Framework_TestCase
{
    public function test()
    {
        /** @var ConnectionInterface|PHPUnit_Framework_MockObject_MockObject $connection */
        $connection = $this->createMock(ConnectionInterface::class);

        $connection->method('select')->with($this->isType('string'))->will($this->onConsecutiveCalls([
            new ArrayObject([
                'cnt' => 1,
            ], ArrayObject::ARRAY_AS_PROPS)],
            [ new ArrayObject([
                'cnt' => 2,
            ], ArrayObject::ARRAY_AS_PROPS)],
            [ new ArrayObject([
                'cnt' => 3,
            ], ArrayObject::ARRAY_AS_PROPS)],
            [ new ArrayObject([
                'cnt' => 4,
            ], ArrayObject::ARRAY_AS_PROPS)],
            [ new ArrayObject([
                'cnt' => 5,
            ], ArrayObject::ARRAY_AS_PROPS)]));

        $service = new StartService($connection);

        $stats = $service->getStatistics();
        $this->assertEquals($stats['members'], 1);
        $this->assertEquals($stats['countries'], 2);
        $this->assertEquals($stats['languages'], 3);
        $this->assertEquals($stats['comments'], 4);
        $this->assertEquals($stats['activities'], 5);
    }
}
