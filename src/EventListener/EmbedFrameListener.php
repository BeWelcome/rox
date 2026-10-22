<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Pages opened with ?embed=1 are shown in a frame of the site itself (the "contact a
 * member" side panel). The site forbids framing (nelmio_security, X-Frame-Options: DENY);
 * these pages may be framed by the site's own pages only. Runs after NelmioSecurityBundle.
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: -1024)]
final class EmbedFrameListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !$event->getRequest()->query->getBoolean('embed')) {
            return;
        }

        $event->getResponse()->headers->set('X-Frame-Options', 'SAMEORIGIN');
    }
}
