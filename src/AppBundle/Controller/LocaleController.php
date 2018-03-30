<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LocaleController extends Controller
{
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

        $this->get('session')->set('lang', $locale);
        $this->get('session')->set('locale', $locale);

        return new RedirectResponse($redirect);
    }
}
