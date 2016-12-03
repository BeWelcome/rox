<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LocaleController extends Controller
{
    /**
     * @var PreferenceService
     */
    protected $preferenceService;

    /**
     * @Route("/rox/in/{locale}", name="language", requirements={"locale" = "[a-z]{2}(-[A-Za-z]{2,})?"}))
     */
    public function selectLocaleAction(Request $request)
    {
        $redirect = $request->headers->get('referer');

        if (!$redirect) {
            $redirect = $this->redirectToRoute('home');
        }

        $locale = $request->attributes->get('locale');

        $this->get('session')->set('locale', $locale);

        $member = $this->getUser();

        if ($member) {
            $this->setLanguageForMember($member, $locale);
        }

        return new RedirectResponse($redirect);
    }

    protected function setLanguageForMember(Member $member, $locale)
    {
        $member;
        $locale;
/*
         $language = $this->languageRepository->getByShortCode($locale);

        $preference = $this->preferenceService
            ->getDefinitionByCode(PreferenceService::PREF_LANG);

        $this->preferenceService
            ->setMemberPreference($member, $preference, $language->id);
*/
    }
}
