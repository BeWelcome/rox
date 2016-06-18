<?php

namespace Rox\I18n\Controller;

use Rox\Core\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class LocaleController extends AbstractController
{
    public function selectLocaleAction(Request $request)
    {
        $redirect = $request->headers->get('referer');

        if (!$redirect) {
            $redirect = $this->getRouter()->generate('home');
        }

        $locale = $request->attributes->get('locale');

        $this->getSession()->set('lang', $locale);

        return new RedirectResponse($redirect);
    }
}
