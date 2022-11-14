<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\MemberPreference;
use App\Entity\Preference;
use App\Form\PreferencesType;
use App\Model\PreferenceModel;
use App\Repository\MemberPreferenceRepository;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PreferenceController extends AbstractController
{
    /**
     * @Route("/members/{username}/preferences", name="preferences")
     */
    public function preferences(
        Request $request,
        Member $member,
        ProfileSubmenu $profileSubmenu,
        PreferenceModel $preferenceModel,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('preferences', ['username' => $loggedInMember->getUsername()]);
        }

        $preferences = $preferenceModel->getPreferences();
        $memberPreferences = $preferenceModel->getMemberPreferences($member, $preferences);
        $data = [];
        foreach ($memberPreferences as $memberPreference) {
            $preference = $memberPreference->getPreference();
            $data[$preference->getCodename()] = $memberPreference->getValue();
        }

        $preferenceForm = $this->createForm(PreferencesType::class, $data, [
            'preferences' => $preferences
        ]);
        $preferenceForm->handleRequest($request);

        if ($preferenceForm->isSubmitted() && $preferenceForm->isValid()) {
            $data = $preferenceForm->getData();

            foreach ($preferences as $preference) {
                $memberPreference->setValue($data[$preference->getCodename()]);
                $entityManager->persist($memberPreference);
            }
            $entityManager->flush();

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        return $this->render('preference/preference.html.twig', [
            'member' => $member,
            'form' => $preferenceForm->createView(),
            'preferences' => $preferences,
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember, [
                'active' => 'preferences',
            ]),
        ]);
    }

    private function getCurrentValues(array $memberPreferences): array
    {
        $data = [];
        foreach ($memberPreferences as $memberPreference) {
            $preference = $memberPreference->getPreference();
            $data[$preference->getCodename()] = $memberPreference->getValue();
        }

        return $data;
    }
}
