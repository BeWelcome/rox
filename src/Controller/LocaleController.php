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
    /**
     * @ParamConverter("language", class="App\Entity\Language", options={"mapping": {"locale": "shortCode"}})
     */
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

        $redirect = $request->headers->get('referer');

        if (!$redirect) {
            // without a referrer (a typed or bookmarked address): the home page
            $redirect = $this->generateUrl('homepage', referenceType: UrlGeneratorInterface::ABSOLUTE_URL);
        }

        $locale = $language->getShortCode();

        $request->setLocale($locale);

        $session = $request->getSession();
        $session->set('lang', $locale);
        $session->set('locale', $locale);
        $session->set('_locale', $locale);

        return new RedirectResponse($redirect);
    }
}
