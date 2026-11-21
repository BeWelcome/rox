<?php

namespace App\EventListener;

use App\Attribute\ReleasesSession;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Saves the session before an action marked #[ReleasesSession] runs: the firewall has read who is logged in by then,
 * and the other requests of the member no longer wait for this one (the session's lock is released with the save).
 */
#[AsEventListener(event: KernelEvents::CONTROLLER_ARGUMENTS)]
final class ReleaseSessionListener
{
    public function __invoke(ControllerArgumentsEvent $event): void
    {
        if (!$event->isMainRequest() || [] === $event->getAttributes(ReleasesSession::class)) {
            return;
        }

        $request = $event->getRequest();
        if ($request->hasSession() && $request->getSession()->isStarted()) {
            $request->getSession()->save();
        }
    }
}
