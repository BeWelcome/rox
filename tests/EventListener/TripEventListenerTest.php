<?php

declare(strict_types=1);

namespace App\Tests\EventListener;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Member;
use App\Entity\Trip;
use App\EventListener\TripEventListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Security;

/**
 * @author Vincent Chalamon <vincentchalamon@gmail.com>
 */
final class TripEventListenerTest extends TestCase
{
    private MockObject $securityMock;
    private MockObject $userMock;
    private MockObject $eventMock;
    private MockObject $objectMock;
    private MockObject $requestMock;
    private TripEventListener $listener;

    protected function setUp(): void
    {
        $this->securityMock = $this->createMock(Security::class);
        $this->userMock = $this->createMock(Member::class);
        $this->eventMock = $this->getMockBuilder(KernelEvent::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods(['getControllerResult'])
            ->onlyMethods(['getRequest'])
            ->getMock();
        $this->objectMock = $this->createMock(Trip::class);
        $this->requestMock = $this->createMock(Request::class);
        $this->listener = new TripEventListener($this->securityMock);
    }

    public function testItGetSubscribedEvents(): void
    {
        $this->assertEquals([
            KernelEvents::VIEW => [
                ['setTripCreator', EventPriorities::PRE_VALIDATE],
            ],
        ], $this->listener::getSubscribedEvents());
    }

    public function testItIgnoresInvalidObject(): void
    {
        $this->eventMock->expects($this->once())->method('getControllerResult')->willReturn(new stdClass());
        $this->securityMock->expects($this->once())->method('getUser')->willReturn($this->userMock);
        $this->objectMock->expects($this->never())->method('setCreator');

        $this->listener->setTripCreator($this->eventMock);
    }

    public function testItIgnoresInvalidUser(): void
    {
        $this->eventMock->expects($this->once())->method('getControllerResult')->willReturn($this->objectMock);
        $this->securityMock->expects($this->once())->method('getUser')->willReturn(null);
        $this->objectMock->expects($this->never())->method('setCreator');

        $this->listener->setTripCreator($this->eventMock);
    }

    public function testItIgnoresInvalidRequest(): void
    {
        $this->eventMock->expects($this->once())->method('getControllerResult')->willReturn($this->objectMock);
        $this->securityMock->expects($this->once())->method('getUser')->willReturn($this->userMock);
        $this->eventMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->requestMock->expects($this->once())->method('getMethod')->willReturn(Request::METHOD_GET);
        $this->objectMock->expects($this->never())->method('setCreator');

        $this->listener->setTripCreator($this->eventMock);
    }

    public function testItSetsTripCreatorFromCurrentUser(): void
    {
        $this->eventMock->expects($this->once())->method('getControllerResult')->willReturn($this->objectMock);
        $this->securityMock->expects($this->once())->method('getUser')->willReturn($this->userMock);
        $this->eventMock->expects($this->once())->method('getRequest')->willReturn($this->requestMock);
        $this->requestMock->expects($this->once())->method('getMethod')->willReturn(Request::METHOD_POST);
        $this->objectMock->expects($this->once())->method('setCreator')->with($this->userMock);

        $this->listener->setTripCreator($this->eventMock);
    }
}
