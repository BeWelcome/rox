<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Pages opened with ?embed=1 are shown in a frame of the site itself (the side panels:
 * contact a member, edit a profile section). The site forbids framing (nelmio_security,
 * X-Frame-Options: DENY); these pages may be framed by the site's own pages only.
 *
 * When such a page sends elsewhere (a form saved, then a redirect to a page that isn't
 * made for the panel), that page couldn't be shown in the frame: the frame gets a short
 * page instead that tells the panel where to go ("bw-embed-leave"). Runs after
 * NelmioSecurityBundle.
 */
#[AsEventListener(event: KernelEvents::RESPONSE, priority: -1024)]
final class EmbedFrameListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest() || !$event->getRequest()->query->getBoolean('embed')) {
            return;
        }

        $response = $event->getResponse();
        if ($response instanceof RedirectResponse && !str_contains($response->getTargetUrl(), 'embed=')) {
            $url = json_encode(
                $response->getTargetUrl(),
                \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_UNESCAPED_SLASHES
            );
            $response = new Response(
                '<!DOCTYPE html><meta charset="utf-8"><script>(function () { var url = ' . $url . ';'
                . ' if (window.parent !== window) { window.parent.postMessage({ type: "bw-embed-leave", url: url }, window.location.origin); }'
                . ' else { window.location.replace(url); } })();</script>',
                Response::HTTP_OK,
                ['Content-Type' => 'text/html; charset=UTF-8', 'Cache-Control' => 'no-store']
            );
            $event->setResponse($response);
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
    }
}
