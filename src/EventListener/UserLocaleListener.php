<?php

namespace App\EventListener;

use App\Entity\Language;
use App\Entity\NewMember as Member;
use Doctrine\ORM\EntityManagerInterface;
use PVars;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @see http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
 */
class UserLocaleListener implements EventSubscriberInterface
{
    /**
     * UserLocaleListener constructor.
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly array $locales)
    {
    }

    /**
     * @SuppressWarnings("PHPMD.StaticAccess")
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        /** @var Member $user */
        $user = $event->getAuthenticationToken()->getUser();
        $locale = $user->getLocale();
        PVars::register('lang', $locale);

        $request->setLocale($locale);
        $session->set('IdLanguage', 0);
        $session->set('_locale', $locale);
        $session->set('lang', $locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }
}
