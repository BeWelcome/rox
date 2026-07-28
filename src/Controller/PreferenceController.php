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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class PreferenceController extends AbstractController
{
    public const string CSRF_TOKEN_ID = 'preference_update';

    public function __construct(
        private readonly PreferenceModel $preferenceModel,
        private readonly EntityManagerInterface $entityManager,
        private readonly BrowserPushSubscriptionRemover $browserPushSubscriptionRemover,
        private readonly BrowserPushPreferenceService $browserPushPreferenceService,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
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
        $csrfToken = $request->headers->get('X-CSRF-Token', '');
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, $csrfToken))) {
            throw new AccessDeniedException('Invalid CSRF token.');
        }

        $form = $this->createFormBuilder(options: ['csrf_protection' => false])
            ->add('member', TextType::class)
            ->add('preference', TextType::class)
            ->add('value', TextType::class)
            ->getForm();

        $form->submit($request->request->all());
        if (!$form->isSubmitted() || !$form->isValid()) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        $data = $form->getData();
        $member = $this->entityManager->getRepository(Member::class)->findOneBy(['username' => $data['member']]);
        if ($member !== $loggedInMember) {
            return new Response(status: Response::HTTP_FORBIDDEN);
        }

        $preference = $this->entityManager
            ->getRepository(Preference::class)
            ->findOneBy(['codename' => $data['preference']])
        ;
        if (!$preference instanceof Preference) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $value = $data['value'];
        $values = $preference->getPossibleValues();
        if ('false' === $value || 'true' === $value) {
            $value = 'false' === $value ? $values[0] : $values[1];
        }
        if (!\in_array($value, $values, true)) {
            return new Response(status: Response::HTTP_BAD_REQUEST);
        }

        $memberPreference = $member->getMemberPreference($preference);
        $memberPreference->setValue($value);
        if (
            Preference::BROWSER_NOTIFICATIONS === $preference->getCodename()
            && BrowserPushPreferenceService::VALUE_ALWAYS !== BrowserPushPreferenceService::normalize($value)
        ) {
            $this->browserPushSubscriptionRemover->removeAllForMember($loggedInMember);
        }

        $this->entityManager->persist($memberPreference);
        $this->entityManager->flush();

        return new Response(status: Response::HTTP_NO_CONTENT);
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
            : BrowserPushPreferenceService::VALUE_NO;
    }
}
