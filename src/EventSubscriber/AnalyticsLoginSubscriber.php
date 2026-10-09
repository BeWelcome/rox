<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Authenticator\RememberMeAuthenticator;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

/**
 * Queues a Plausible "Login" event for interactive logins (#540). It is sent
 * by templates/_analytics.html.twig on the page the member lands on.
 * Automatic logins through the remember-me cookie are not counted.
 */
class AnalyticsLoginSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        if ($event->getAuthenticator() instanceof RememberMeAuthenticator) {
            return;
        }

        $request = $event->getRequest();
        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();
        if ($session instanceof Session) {
            $session->getFlashBag()->add('plausible_event', 'Login');
        }
    }
}
