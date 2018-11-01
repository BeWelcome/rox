<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Listens for access denied exceptions and redirects if the route has
 * access_denied_redirect configured. Useful for redirecting the login page to
 * home if the user is already logged in, for example.
 */
class AccessDeniedRedirectListener
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (!$event->getException() instanceof AccessDeniedHttpException
            && !$event->getException() instanceof AccessDeniedException) {
            return;
        }

        $request = $event->getRequest();

        $redirectRoute = $request->attributes->get('access_denied_redirect');

        if (!$redirectRoute) {
            return;
        }

        $url = $this->urlGenerator->generate($redirectRoute);

        $response = new RedirectResponse($url);

        $event->setResponse($response);
    }
}
