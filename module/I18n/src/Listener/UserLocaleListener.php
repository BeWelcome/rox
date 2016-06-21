<?php

namespace Rox\I18n\Listener;

use Rox\Core\Exception\NotFoundException;
use Rox\I18n\Model\Language as LanguageRepository;
use Rox\Member\Model\Member;
use Rox\Member\Service\PreferenceService;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

/**
 * @see http://symfony.com/doc/current/cookbook/session/locale_sticky_session.html
 */
class UserLocaleListener
{
    /**
     * @var SessionInterface
     */
    protected $session;

    /**
     * @var PreferenceService
     */
    protected $preferenceService;

    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    public function __construct(
        SessionInterface $session,
        PreferenceService $preferenceService,
        LanguageRepository $languageRepository
    ) {
        $this->session = $session;
        $this->preferenceService = $preferenceService;
        $this->languageRepository = $languageRepository;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        /** @var Member $user */
        $user = $event->getAuthenticationToken()->getUser();

        try {
            $language = $this->preferenceService
                ->getMemberPreferenceByCode($user, PreferenceService::PREF_LANG);
        } catch (NotFoundException $e) {
            // User doesn't have a language preference set.
            return;
        }

        // Now we know the language preference, continue using the language ID.
        $langId = $language->pivot->Value;

        $language = $this->languageRepository->getById($langId);

        $this->session->set('lang', $language->ShortCode);
    }
}
