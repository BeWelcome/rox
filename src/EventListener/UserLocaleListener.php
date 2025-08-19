<?php

namespace App\EventListener;

use App\Entity\Language;
use App\Entity\Member;
use Doctrine\ORM\EntityManager;
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
        $user->initializePreferredLanguage($this->entityManager);

        $language = $user->getPreferredLanguage();
        if (null === $language) {
            $language = $request->getPreferredLanguage($this->locales);

            // \todo: Search for a matching language in the list of UI languages

            $languageRepository = $this->entityManager->getRepository(Language::class);
            $language = $languageRepository->findOneBy([
                'shortCode' => $language,
            ]);
            $locale = (null === $language) ? 'en' : $language->getShortCode();
        } else {
            $locale = $language->getShortCode();
        }
        PVars::register('lang', $locale);

        $request->setLocale($locale);
        $session->set('IdLanguage', $language->getId());
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
