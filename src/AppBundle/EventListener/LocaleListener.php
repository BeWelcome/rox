<?php

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Makes sure the locale is stored in the current session for all responses.
 *
 * @see http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
 */
class LocaleListener implements EventSubscriberInterface
{
    /**
     * Stores the default locale.
     *
     * @var string
     */
    private $defaultLocale;

    /**
     * LocaleListener constructor.
     *
     * @param string $defaultLocale The default locale
     */
    public function __construct($defaultLocale = 'en')
    {
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * {@inheritdoc}
     *
     * @param GetResponseEvent $event Triggered Event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (!$request->hasPreviousSession()) {
            return;
        }

        $session = $request->getSession();

        $request->setLocale($session->get('lang', $this->defaultLocale));
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            // must be registered after the default Locale listener
            KernelEvents::REQUEST => [
                [
                    'onKernelRequest',
                    15,
                ],
            ],
        ];
    }
}
