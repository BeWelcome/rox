<?php

namespace App\EventListener;

use App\Entity\Language;
use App\Entity\Member;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use PVars;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @see http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
 */
class UserLocaleListener implements EventSubscriberInterface
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * UserLocaleListener constructor.
     *
     * @param SessionInterface       $session
     * @param EntityManagerInterface $em
     */
    public function __construct(SessionInterface $session, EntityManagerInterface $em)
    {
        $this->session = $session;
        $this->em = $em;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     *
     * @param InteractiveLoginEvent $event
     */
    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $request = $event->getRequest();

        /** @var Member $user */
        $user = $event->getAuthenticationToken()->getUser();

        $language = $user->getPreferredLanguage();
        if ($language) {
            $locale = $language->getShortCode();
        } else {
            $locale = $this->session->get('_locale', 'en');
            $languageRepository = $this->em->getRepository(Language::class);
            $language = $languageRepository->findOneBy([
                'shortcode' => $locale,
            ]);
        }
        PVars::register('lang', $locale);

        $request->setLocale($locale);
        $this->session->set('IdLanguage', $language->getId());
        $this->session->set('_locale', $locale);
        $this->session->set('lang', $locale);
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }
}
