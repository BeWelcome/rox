<?php

namespace App\Tests\EventListener;

use App\EventListener\EmbedFrameListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * A page in a side panel (?embed=1) that redirects: back to a conversation it stays in the
 * panel, anywhere else the panel is told to leave.
 */
class EmbedFrameListenerTest extends TestCase
{
    public function testRedirectToConversationStaysInThePanel(): void
    {
        $event = $this->redirect('/conversation/112#last');

        (new EmbedFrameListener())($event);

        $response = $event->getResponse();
        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('/conversation/112?embed=1#last', $response->getTargetUrl());
    }

    public function testRedirectElsewhereLeavesThePanel(): void
    {
        $event = $this->redirect('/members/member-6');

        (new EmbedFrameListener())($event);

        $response = $event->getResponse();
        self::assertNotInstanceOf(RedirectResponse::class, $response);
        self::assertStringContainsString('bw-embed-leave', (string) $response->getContent());
    }

    private function redirect(string $target): ResponseEvent
    {
        return new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/conversation/112/reply?embed=1', 'POST'),
            HttpKernelInterface::MAIN_REQUEST,
            new RedirectResponse($target)
        );
    }
}
