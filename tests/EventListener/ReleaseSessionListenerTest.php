<?php

namespace App\Tests\EventListener;

use App\Attribute\ReleasesSession;
use App\EventListener\ReleaseSessionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Sessions are locked, one request of a member at a time: an action that only reads the session gives it back
 * before it runs, so the pictures, widgets and counters of a page don't wait in line.
 */
final class ReleaseSessionListenerTest extends TestCase
{
    public function testTheSessionIsSavedBeforeAMarkedActionRuns(): void
    {
        $session = $this->startedSession();

        new ReleaseSessionListener()($this->event($session, [$this, 'marked']));

        self::assertFalse($session->isStarted());
        // what was in it stays, and it opens again when it is needed
        self::assertSame('value', $session->get('key'));
    }

    public function testOtherActionsKeepTheSession(): void
    {
        $session = $this->startedSession();

        new ReleaseSessionListener()($this->event($session, [$this, 'unmarked']));

        self::assertTrue($session->isStarted());
    }

    public function testASubRequestKeepsTheSession(): void
    {
        $session = $this->startedSession();

        new ReleaseSessionListener()($this->event($session, [$this, 'marked'], HttpKernelInterface::SUB_REQUEST));

        self::assertTrue($session->isStarted());
    }

    #[ReleasesSession]
    public function marked(): void
    {
    }

    public function unmarked(): void
    {
    }

    private function startedSession(): Session
    {
        $session = new Session(new MockArraySessionStorage());
        $session->set('key', 'value');
        self::assertTrue($session->isStarted());

        return $session;
    }

    /**
     * @param array{0: object, 1: string} $controller
     */
    private function event(Session $session, array $controller, int $type = HttpKernelInterface::MAIN_REQUEST): ControllerArgumentsEvent
    {
        $request = new Request();
        $request->setSession($session);

        return new ControllerArgumentsEvent($this->createStub(HttpKernelInterface::class), $controller, [], $request, $type);
    }
}
