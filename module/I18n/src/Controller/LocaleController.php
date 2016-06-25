<?php

namespace Rox\I18n\Controller;

use Rox\Core\Controller\AbstractController;
use Rox\I18n\Model\Language as LanguageRepository;
use Rox\Member\Model\Member;
use Rox\Member\Service\PreferenceService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LocaleController extends AbstractController
{
    /**
     * @var PreferenceService
     */
    protected $preferenceService;

    /**
     * @var LanguageRepository
     */
    protected $languageRepository;

    public function __construct(
        PreferenceService $preferenceService,
        LanguageRepository $languageRepository
    ) {
        $this->preferenceService = $preferenceService;
        $this->languageRepository = $languageRepository;
    }

    public function selectLocaleAction(Request $request)
    {
        $redirect = $request->headers->get('referer');

        if (!$redirect) {
            $redirect = $this->getRouter()->generate('home');
        }

        $locale = $request->attributes->get('locale');

        $this->getSession()->set('lang', $locale);

        $member = $this->getMember();

        if ($member) {
            $this->setLanguageForMember($member, $locale);
        }

        return new RedirectResponse($redirect);
    }

    protected function setLanguageForMember(Member $member, $locale)
    {
        $language = $this->languageRepository->getByShortCode($locale);

        $preference = $this->preferenceService
            ->getDefinitionByCode(PreferenceService::PREF_LANG);

        $this->preferenceService
            ->setMemberPreference($member, $preference, $language->id);
    }
}
