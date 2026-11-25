<?php

namespace App\EventListener;

use App\Attribute\ReleasesSession;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Saves the session before an action marked #[ReleasesSession] runs: the firewall has read who is logged in by then,
 * and the other requests of the member no longer wait for this one (the session's lock is released with the save).
 *
 * The session stays closed from then on. The firewall would open it again when the request ends, to write back who
 * is logged in (ContextListener::onKernelResponse). If the member logged out meanwhile, their session is gone: PHP
 * starts a new one for the id it no longer knows, the member is written into it, and its cookie logs them in again.
 * So when the session already says what the firewall would write, the firewall is told to leave it.
 */
#[AsEventListener(event: KernelEvents::CONTROLLER_ARGUMENTS)]
final class ReleaseSessionListener
{
    /**
     * Set on the request by the firewall's ContextListener when it has read the session, to the session key of the
     * logged-in member; only then does it write back when the request ends (it has no other switch).
     */
    public const string FIREWALL_RAN = '_security_firewall_run';

    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function __invoke(ControllerArgumentsEvent $event): void
    {
        if (!$event->isMainRequest() || [] === $event->getAttributes(ReleasesSession::class)) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->hasSession() || !$request->getSession()->isStarted()) {
            return;
        }
        $session = $request->getSession();

        // Nothing to write back: a member who is in the session, or nobody and nobody in it. A member who is not in
        // it yet ("Stay logged in" has just logged them in) is still written, or every request would log them in anew.
        $key = $request->attributes->get(self::FIREWALL_RAN);
        if (\is_string($key) && $session->has($key) === (null !== $this->tokenStorage->getToken())) {
            $request->attributes->remove(self::FIREWALL_RAN);
        }

        $session->save();
    }
}
