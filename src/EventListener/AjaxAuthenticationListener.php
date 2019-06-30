<?php

// src/AppBundle/EventListener/AjaxAuthenticationListener.php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 */
class AjaxAuthenticationListener
{

    /**
     * Handles security related exceptions.
     *
     * @param ExceptionEvent $event
     */
    public function onCoreException(ExceptionEvent $event)
    {
        $exception = $event->getException();
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            if ($exception instanceof AuthenticationException || $exception instanceof AccessDeniedException) {
                $event->setResponse(new Response('', 401));
            }
        }
    }
}
