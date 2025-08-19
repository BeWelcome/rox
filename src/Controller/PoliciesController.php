<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PoliciesController extends AbstractController
{
    private readonly array $documentLocales;

    public function __construct(string $locales)
    {
        $this->documentLocales = explode(',', $locales);
    }

    #[Route(path: '/terms/{locale}', name: 'terms_of_use')]
    public function showTermsOfUse(Request $request, ?string $locale = null): Response
    {
        $locale = $this->ensureLocaleAllowed($locale, $request);

        if (null !== $locale && 'en' !== $locale && 'fr' !== $locale) {
            return $this->render('policies/tou_translated.html.twig', [
                'policy_french' => $this->generateUrl('terms_of_use', ['locale' => 'fr']),
                'policy_english' => $this->generateUrl('terms_of_use', ['locale' => 'en']),
                'locale' => $locale,
            ]);
        }

        return $this->render('policies/terms.' . $locale . '.html.twig');
    }

    #[Route(path: '/privacy/{locale}', name: 'privacy_policy')]
    public function showPrivacyPolicy(Request $request, ?string $locale = null): Response
    {
        $locale = $this->ensureLocaleAllowed($locale, $request);

        if (null !== $locale && 'en' !== $locale && 'fr' !== $locale) {
            return $this->render('policies/pp_translated.html.twig', [
                'policy_french' => $this->generateUrl('privacy_policy', ['locale' => 'fr']),
                'policy_english' => $this->generateUrl('privacy_policy', ['locale' => 'en']),
            ]);
        }

        return $this->render('policies/privacy.' . $locale . '.html.twig');
    }

    #[Route(path: '/datarights/{locale}', name: 'data_rights')]
    public function showDataRights(Request $request, ?string $locale = null): Response
    {
        $locale = $this->ensureLocaleAllowed($locale, $request);

        if (null !== $locale && 'en' !== $locale && 'fr' !== $locale) {
            return $this->render('policies/dp_translated.html.twig', [
                'policy_french' => $this->generateUrl('data_rights', ['locale' => 'fr']),
                'policy_english' => $this->generateUrl('data_rights', ['locale' => 'en']),
            ]);
        }

        return $this->render('policies/datarights.' . $locale . '.html.twig');
    }

    private function ensureLocaleAllowed(?string $locale, Request $request): string
    {
        $locale ??= $request->getLocale();

        if (!\in_array($locale, $this->documentLocales, true)) {
            $locale = 'en';
        }

        return $locale;
    }
}
