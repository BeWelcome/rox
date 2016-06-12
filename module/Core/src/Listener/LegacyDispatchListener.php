<?php

namespace Rox\Core\Listener;

use Rox\Core\Kernel\LegacyHttpKernel;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class LegacyDispatchListener
{
    /**
     * @var LegacyHttpKernel
     */
    protected $kernel;

    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(LegacyHttpKernel $kernel, SessionInterface $session)
    {
        $this->kernel = $kernel;
        $this->session = $session;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Only act if the exception was a 404 error, we will then try to route
        // it with the legacy kernel.
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        // Kick-start the Symfony session. This replaces session_start() in the
        // old code, which is now turned off.
        $this->session->start();

        try {
            $response = $this->kernel->handle($event->getRequest(), $event->getRequestType());
        } catch (ResourceNotFoundException $e) {
            // If the legacy kernel also failed to route the request, let the
            // original error bubble back up to the new Symfony error handler.
            return;
        }

        $event->setResponse($response);
    }
}
