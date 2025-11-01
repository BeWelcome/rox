<?php

namespace App\Controller;

use App\Entity\Language;
use App\Entity\NewMember as Member;
use App\Entity\MemberPreference;
use App\Entity\Preference;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

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
        /** @var Member $member */
        $member = $this->getUser();
        if ($member) {
            $preference = $entityManager->getRepository(Preference::class)->findOneBy([
                'codename' => Preference::LOCALE,
            ]);
            $memberPreference = $entityManager->getRepository(MemberPreference::class)->findOneBy([
                'preference' => $preference,
                'member' => $member,
            ]);
            if (null === $memberPreference) {
                $memberPreference = new MemberPreference();
                $memberPreference
                    ->setMember($member)
                    ->setPreference($preference)
                ;
            }
            $memberPreference->setValue($language->getShortCode());
            $entityManager->persist($memberPreference);
            $entityManager->flush();
        }

        $redirect = $request->headers->get('referer');

        if (!$redirect) {
            $redirect = $this->redirectToRoute('homepage');
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
