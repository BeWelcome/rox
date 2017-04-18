<?php

namespace AppBundle\EventListener;

use AppBundle\LegacyKernel\LegacyHttpKernel;
use EnvironmentExplorer;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    public function __construct(LegacyHttpKernel $kernel, SessionInterface $session, TokenStorage $tokenStorage)
    {
        $this->kernel = $kernel;
        $this->session = $session;
        $this->tokenStorage = $tokenStorage;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        // Only act if the exception was a 404 error, we will then try to route
        // it with the legacy kernel.
        if (!$event->getException() instanceof NotFoundHttpException) {
            return;
        }

        // If the Symfony router matched the request but we have a 404, it means
        // the controller probably threw the 404 error, so don't try legacy
        // dispatch.
        if ($event->getRequest()->attributes->get('_route')) {
            return;
        }

        $container = $this->kernel->getContainer();
        $environmentExplorer = new EnvironmentExplorer();
        $environmentExplorer->initializeGlobalState(
            $container->getParameter('database_host'),
            $container->getParameter('database_name'),
            $container->getParameter('database_user'),
            $container->getParameter('database_password')
        );

        // Kick-start the Symfony session. This replaces session_start() in the
        // old code, which is now turned off.
        $this->session->start();
        if (!$this->session->has('IdMember')) {
            $rememberMeToken = unserialize($this->session->get('_security_default'));
            if ($rememberMeToken === null) {
                throw new AccessDeniedException();
            }

            $user = $rememberMeToken->getUser();
            if ($user !== null) {
                $this->session->set('IdMember', $user->getId());
            }
        }
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
