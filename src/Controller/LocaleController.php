<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class LocaleController extends AbstractController
{
    /**
     * @Route("/rox/in/{locale}", name="language", requirements={"locale" = "[a-z]{2}(-[A-Za-z]{2,})?"})
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function selectLocaleAction(Request $request)
    {
        $redirect = $request->headers->get('referer');

        if (!$redirect) {
            $redirect = $this->redirectToRoute('home');
        }

        $locale = $request->attributes->get('locale');

        $this->get('session')->set('lang', $locale);
        $this->get('session')->set('locale', $locale);
        $this->get('session')->set('_locale', $locale);

        return new RedirectResponse($redirect);
    }
}
