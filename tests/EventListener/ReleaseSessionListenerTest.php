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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\InMemoryUser;

/**
 * Sessions are locked, one request of a member at a time: an action that only reads the session gives it back
 * before it runs, so the pictures, widgets and counters of a page don't wait in line.
 */
final class ReleaseSessionListenerTest extends TestCase
{
    /** the session key of the logged-in member, as the firewall "main" names it */
    private const string KEY = '_security_main';

    public function testTheSessionIsSavedBeforeAMarkedActionRuns(): void
    {
        $session = $this->startedSession();

        $this->listener()($this->event($session, [$this, 'marked']));

        self::assertFalse($session->isStarted());
        // what was in it stays, and it opens again when it is needed
        self::assertSame('value', $session->get('key'));
    }

    public function testOtherActionsKeepTheSession(): void
    {
        $session = $this->startedSession();

        $this->listener()($this->event($session, [$this, 'unmarked']));

        self::assertTrue($session->isStarted());
    }

    public function testASubRequestKeepsTheSession(): void
    {
        $session = $this->startedSession();

        $this->listener()($this->event($session, [$this, 'marked'], HttpKernelInterface::SUB_REQUEST));

        self::assertTrue($session->isStarted());
    }

    /**
     * The firewall opens the session again when the request ends, to write back who is logged in. After a logout
     * in between that started a new session with the member in it.
     */
    public function testTheFirewallLeavesAReleasedSessionThatAlreadyHasTheMember(): void
    {
        $session = $this->startedSession();
        $session->set(self::KEY, 'the member, serialized');
        $event = $this->event($session, [$this, 'marked'], firewallRan: true);

        $this->listener(loggedIn: true)($event);

        self::assertFalse($event->getRequest()->attributes->has(ReleaseSessionListener::FIREWALL_RAN));
    }

    public function testTheFirewallLeavesAReleasedSessionOfAVisitor(): void
    {
        $event = $this->event($this->startedSession(), [$this, 'marked'], firewallRan: true);

        $this->listener(loggedIn: false)($event);

        self::assertFalse($event->getRequest()->attributes->has(ReleaseSessionListener::FIREWALL_RAN));
    }

    /**
     * "Stay logged in" has just logged the member in: the session doesn't have them yet.
     */
    public function testAMemberWhoIsNotInTheSessionYetIsStillWritten(): void
    {
        $event = $this->event($this->startedSession(), [$this, 'marked'], firewallRan: true);

        $this->listener(loggedIn: true)($event);

        self::assertSame(self::KEY, $event->getRequest()->attributes->get(ReleaseSessionListener::FIREWALL_RAN));
    }

    /**
     * The member of the session may not go on (banned meanwhile): the firewall takes them out of it.
     */
    public function testAMemberWhoIsNoLongerLoggedInIsStillTakenOut(): void
    {
        $session = $this->startedSession();
        $session->set(self::KEY, 'the member, serialized');
        $event = $this->event($session, [$this, 'marked'], firewallRan: true);

        $this->listener(loggedIn: false)($event);

        self::assertSame(self::KEY, $event->getRequest()->attributes->get(ReleaseSessionListener::FIREWALL_RAN));
    }

    public function testOtherActionsAreWrittenBackAsBefore(): void
    {
        $session = $this->startedSession();
        $session->set(self::KEY, 'the member, serialized');
        $event = $this->event($session, [$this, 'unmarked'], firewallRan: true);

        $this->listener(loggedIn: true)($event);

        self::assertSame(self::KEY, $event->getRequest()->attributes->get(ReleaseSessionListener::FIREWALL_RAN));
    }

    #[ReleasesSession]
    public function marked(): void
    {
    }

    public function unmarked(): void
    {
    }

    private function listener(bool $loggedIn = false): ReleaseSessionListener
    {
        $tokenStorage = new TokenStorage();
        if ($loggedIn) {
            $tokenStorage->setToken(new UsernamePasswordToken(new InMemoryUser('member', null), 'main'));
        }

        return new ReleaseSessionListener($tokenStorage);
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
    private function event(
        Session $session,
        array $controller,
        int $type = HttpKernelInterface::MAIN_REQUEST,
        bool $firewallRan = false,
    ): ControllerArgumentsEvent {
        $request = new Request();
        $request->setSession($session);
        if ($firewallRan) {
            $request->attributes->set(ReleaseSessionListener::FIREWALL_RAN, self::KEY);
        }

        return new ControllerArgumentsEvent($this->createStub(HttpKernelInterface::class), $controller, [], $request, $type);
    }
}
