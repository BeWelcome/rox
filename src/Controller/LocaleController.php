<?php

namespace App\Controller;

use App\Entity\Language;
use App\Entity\Member;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
    /**
     * @Route("/rox/in/{locale}", name="language", requirements={"locale" = "[a-z]{2}(-[A-Za-z]{2,})?"})
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortcode"}})
     *
     * @return RedirectResponse
     */
    public function selectLocaleAction(Request $request, Language $language)
    {
        /** @var Member $member */
        $member = $this->getUser();
        if ($member) {
            $member->setPreferredLanguage($language);
        }

        $redirect = $request->headers->get('referer');

        if (!$redirect) {
            $redirect = $this->redirectToRoute('homepage');
        }

        $locale = $request->attributes->get('locale');

        $this->get('session')->set('lang', $locale);
        $this->get('session')->set('locale', $locale);
        $this->get('session')->set('_locale', $locale);

        return new RedirectResponse($redirect);
    }
}
