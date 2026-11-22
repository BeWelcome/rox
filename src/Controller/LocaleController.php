<?php

namespace App\Controller;

use App\Entity\Language;
use App\Entity\Member;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LocaleController extends AbstractController
{
    #[Route(path: '/rox/in/{locale}', name: 'language', requirements: ['locale' => '[a-z]{2}(-[A-Za-z]{2,})?'])]
    public function selectLocaleAction(
        Request $request,
        #[MapEntity(mapping: ['locale' => 'shortCode'])] Language $language,
        EntityManagerInterface $entityManager,
    ): RedirectResponse {
        // A member keeps the language: the login and the mails sent to them use it (Member::locale)
        $member = $this->getUser();
        if ($member instanceof Member) {
            $member->setLocale($language->getShortCode());
            $entityManager->flush();
        }

        // without a referrer (a typed or bookmarked address) or from another site: the home page
        $redirect = $this->pageOfThisSite($request, $request->headers->get('referer'))
            ?? $this->generateUrl('homepage', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);

        $locale = $language->getShortCode();

        $request->setLocale($locale);

        $session = $request->getSession();
        $session->set('lang', $locale);
        $session->set('locale', $locale);
        $session->set('_locale', $locale);

        return new RedirectResponse($redirect);
    }

    /**
     * The referrer's page on this site, or null when the referrer is no page of it (another site, an address without
     * a host, javascript: and the like). The address is put together from the request's own host and the referrer's
     * path and query: browsers read some addresses differently than parse_url() (a backslash is a slash to them), so
     * the referrer itself is never sent back.
     */
    private function pageOfThisSite(Request $request, ?string $referrer): ?string
    {
        if (null === $referrer || '' === $referrer) {
            return null;
        }

        $parts = parse_url($referrer);
        if (
            false === $parts
            || !\in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)
            || 0 !== strcasecmp($parts['host'] ?? '', $request->getHost())
        ) {
            return null;
        }

        $page = $request->getSchemeAndHttpHost() . '/' . ltrim($parts['path'] ?? '', '/');
        if (isset($parts['query'])) {
            $page .= '?' . $parts['query'];
        }

        return $page;
    }
}
