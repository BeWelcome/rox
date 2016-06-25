<?php

namespace Rox\I18n\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * @see http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
 */
class LocaleListener
{
    /**
     * @var string
     */
    protected $defaultLocale;

    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        $session = $request->getSession();

        $request->setLocale($session->get('lang', $this->defaultLocale));
    }
}
