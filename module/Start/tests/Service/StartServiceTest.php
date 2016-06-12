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

        $connection->method('select')->with($this->isType('string'))->willReturn([
            new ArrayObject([
                'cnt' => 12,
            ], ArrayObject::ARRAY_AS_PROPS),
        ]);

        $service = new StartService($connection);

        $service->getStatistics();
    }
}
