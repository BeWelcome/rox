<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\Preference;
use App\Form\PreferencesType;
use App\Model\PreferenceModel;
use App\Service\BrowserPushConfig;
use App\Service\BrowserPushPreferenceService;
use App\Service\BrowserPushSubscriptionRemover;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ProfileSubmenu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PreferenceController extends AbstractController
{
    public function __construct(
        private readonly PreferenceModel $preferenceModel,
        private readonly EntityManagerInterface $entityManager,
        private readonly BrowserPushSubscriptionRemover $browserPushSubscriptionRemover,
        private readonly BrowserPushPreferenceService $browserPushPreferenceService,
    ) {
    }

    #[Route(path: '/mypreferences', name: 'mypreferences_redirect')]
    public function redirectMyPreferences(): RedirectResponse
    {
        return $this->redirectToRoute('preferences', ['username' => $this->getUser()->getUsername()]);
    }

    #[Route(path: '/members/{username:member}/preferences', name: 'preferences')]
    public function preferences(
        Request $request,
        Member $member,
        ProfileSubmenu $profileSubmenu,
        ChangeProfilePictureGlobals $globals,
        BrowserPushConfig $browserPushConfig,
    ): Response {
        /** Member must be the logged in member to be able to access this page.
         * @var Member $loggedInMember
         */
        $loggedInMember = $this->getUser();
        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('preferences', ['username' => $loggedInMember->getUsername()]);
        }

        $preferences = $this->preferenceModel->getPreferences();
        if (!$browserPushConfig->isConfigured()) {
            $preferences = array_filter(
                $preferences,
                static fn (Preference $preference): bool => Preference::BROWSER_NOTIFICATIONS
                    !== $preference->getCodename()
            );
        }
        $memberPreferences = $this->preferenceModel->getMemberPreferences($loggedInMember, $preferences);
        $data = [];
        foreach ($memberPreferences as $memberPreference) {
            $preference = $memberPreference->getPreference();
            $data[$preference->getCodename()] = Preference::BROWSER_NOTIFICATIONS === $preference->getCodename()
                ? $this->browserPushPreferenceService->getValue($loggedInMember)
                : $memberPreference->getValue();
        }

        $preferenceForm = $this->createForm(PreferencesType::class, $data, [
            'preferences' => $preferences,
        ]);

        return $this->render('preference/preference.html.twig', [
            'member' => $loggedInMember,
            'form' => $preferenceForm,
            'preferences' => $preferences,
            'browser_push_enabled' => $browserPushConfig->isConfigured(),
            'browser_push_public_key' => $browserPushConfig->getPublicKey(),
            'browser_push_preference' => $this->getBrowserPushPreferenceValue($loggedInMember, $browserPushConfig),
            'globals_js_json' => $globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $profileSubmenu->getSubmenu($loggedInMember, $member, [
                'active' => 'preferences',
            ]),
        ]);
    }

    #[Route(path: '/members/update/preference', name: 'profile_update_preference', methods: ['POST'], priority: 20)]
    public function updatePreference(Request $request): Response
    {
        $form = $this->createFormBuilder(options: ['csrf_protection' => false])
            ->add('member', TextType::class)
            ->add('preference', TextType::class)
            ->add('value', TextType::class)
            ->getForm();

        $form->submit($request->request->all());
        if ($form->isSubmitted() && $form->isValid()) {
            $loggedInMember = $this->getUser();
            $data = $form->getData();
            $memberRepository = $this->entityManager->getRepository(Member::class);
            $member = $memberRepository->findOneBy(['username' => $data['member']]);

            // Check if user exists and if logged in member is either same or privileged
            if ($member === $loggedInMember) {
                $preference = $this->entityManager
                    ->getRepository(Preference::class)
                    ->findOneBy(['codename' => $data['preference']])
                ;
                $memberPreference = $member->getMemberPreference($preference);

                $value = $data['value'];
                if ('false' === $data['value'] || 'true' === $data['value']) {
                    $values = $preference->getPossibleValues();
                    $value = 'false' === $data['value'] ? $values[0] : $values[1];
                }

                $memberPreference->setValue($value);
                if (
                    Preference::BROWSER_NOTIFICATIONS === $preference->getCodename()
                    && BrowserPushPreferenceService::VALUE_ALWAYS !== BrowserPushPreferenceService::normalize($value)
                ) {
                    $this->browserPushSubscriptionRemover->removeAllForMember($loggedInMember);
                }

                $this->entityManager->persist($memberPreference);
                $this->entityManager->flush();
            }
        }

        return new Response();
    }

    private function getBrowserPushPreferenceValue(Member $member, BrowserPushConfig $browserPushConfig): string
    {
        if (!$browserPushConfig->isConfigured()) {
            return 'No';
        }

        $preference = $this->entityManager->getRepository(Preference::class)->findOneBy([
            'codename' => Preference::BROWSER_NOTIFICATIONS,
        ]);

        return $preference instanceof Preference
            ? $this->browserPushPreferenceService->getValue($member)
            : BrowserPushPreferenceService::VALUE_ALWAYS;
    }
}
