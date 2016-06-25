<?php

namespace Rox\Member\Listener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Class HttpsCheckListener.
 */
class HttpsCheckListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->isSecure()) {
            return;
        }

        if (!$request->cookies->get('use_https')) {
            return;
        }

        $uri = preg_replace('/^http/', 'https', $request->getUri());

        $response = new RedirectResponse($uri);

        $event->setResponse($response);
    }
}
