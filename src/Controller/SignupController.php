<?php

namespace App\Controller;

use App\Doctrine\AccommodationType;
use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Form\ConfirmEmailAddressFormType;
use App\Form\SignupFormFinalizeType;
use App\Form\SignupFormType;
use App\Model\SignupModel;
use App\Service\Mailer;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsCsrfTokenValid;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

/**
 * @infection-ignore-all
 */
class SignupController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public const string RESEND_TOKEN = 'resend_confirmation';

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/signup', name: 'signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        SignupModel $signupModel,
        Security $security,
        array $locales,
        RateLimiterFactoryInterface $signupIpLimiter,
    ): Response {
        $signupFormData = ['locale' => $request->getLocale()];
        if ($request->isMethod('POST')) {
            $signupFormData['username'] = $request->attributes->get('username');
        }

        $loggedInMember = $this->getUser();

        if (null !== $loggedInMember) {
            return $this->redirectToRoute('homepage');
        }

        $signupForm = $this->createForm(SignupFormType::class, $signupFormData);
        $signupForm->handleRequest($request);

        // Every submission counts, also invalid ones, so bots trying many names from one address are stopped too.
        $tooManySignups = $signupForm->isSubmitted()
            && !$signupIpLimiter->create($request->getClientIp())->consume()->isAccepted();
        if ($tooManySignups) {
            $signupForm->addError(new FormError($this->getTranslator()->trans('signup.error.too.many')));
        }

        if ($signupForm->isSubmitted() && $signupForm->isValid()) {
            $signupData = $signupForm->getData();
            if (!$signupModel->checkUsername($signupData['username'])) {
                $this->setUsernameOrEmailNotUniqueError($signupForm);
            }
            if (!$signupModel->checkEmailAddress($signupData['email'])) {
                $this->setUsernameOrEmailNotUniqueError($signupForm);
            }
            $errors = $signupForm->getErrors(true);
            $errorCount = $errors->count();
            if (0 === $errorCount) {
                $member = $signupModel->createAccount($signupData);

                return $security->login($member, 'form_login');
            }
        }

        return $this->render('signup/first.step.html.twig', [
            'signup' => $signupForm->createView(),
        ], new Response(status: $tooManySignups ? Response::HTTP_TOO_MANY_REQUESTS : Response::HTTP_OK));
    }

    #[Route(path: '/members/{username:member}/finalize', name: 'finish_setup', priority: 11)]
    public function finishSetup(
        Request $request,
        Member $member,
        SignupModel $signupModel,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        if (
            !\in_array(
                $member->getStatus(),
                [MemberStatusType::AWAITING_MAIL_CONFIRMATION, MemberStatusType::MAIL_CONFIRMED],
                true
            )
        ) {
            // The setup is done: only the email address may still wait for its confirmation
            if (MemberStatusType::ACCOUNT_ACTIVATED === $member->getStatus()) {
                return $this->redirectToRoute('confirm_email', ['username' => $member->getUsername()]);
            }

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $finalizeForm = $this->createForm(
            SignupFormFinalizeType::class,
            options: ['show_registration_key' => null !== $member->getRegistrationKey()]
        );
        $finalizeForm->handleRequest($request);

        if ($finalizeForm->isSubmitted() && $finalizeForm->isValid()) {
            $registrationKey = $finalizeForm->get('registration_key')->getData();
            if (
                AccommodationType::YES === $finalizeForm->get('accommodation')->getData()
                && '0' === $finalizeForm->get('hosting_interest')->getData()
            ) {
                $finalizeForm
                    ->get('hosting_interest')
                    ->addError(new FormError($this->translator->trans('error.hosting.interest')))
                ;
            } elseif (null !== $registrationKey && $registrationKey !== $member->getRegistrationKey()) {
                // a wrong key would save the setup with the address still unconfirmed, without a word
                $finalizeForm
                    ->get('registration_key')
                    ->addError(new FormError($this->translator->trans('error.registration.key')))
                ;
            } else {
                $signupModel->updateMember($member, $finalizeForm->getData());
                $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $member->getUsername());
                if (MemberStatusType::ACCOUNT_ACTIVATED === $member->getStatus()) {
                    // saved without the key: the account starts with the confirmation (the bell leads there)
                    $this->addTranslatedFlash('notice', 'flash.signup.saved.unconfirmed');
                }

                return $this->redirectToRoute('profile_edit', ['username' => $member->getUsername()]);
            }
        }

        return $this->render('signup/finalize.html.twig', [
            'hide_finish_setup' => true,
            'member' => $member,
            'finalize' => $finalizeForm->createView(),
        ]);
    }

    /**
     * The confirmation mail again, from the confirm page (for a member whose mail got lost or expired in the spam
     * folder). Only for the logged-in member and while the address waits for its confirmation.
     */
    #[Route(path: '/members/{username:member}/resend', name: 'resend_confirmation_email', methods: ['GET', 'POST'], priority: 1)]
    #[IsCsrfTokenValid(self::RESEND_TOKEN, methods: ['POST'])]
    public function resendConfirmationEmail(
        Request $request,
        Member $member,
        Mailer $mailer,
        RateLimiterFactoryInterface $confirmationMailLimiter,
    ): Response {
        $username = $member->getUsername();
        if ($member !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        // an old link: the confirm page, where the member can ask for the mail
        if (!$request->isMethod('POST')) {
            return $this->redirectToRoute('confirm_email', ['username' => $username]);
        }

        if (null === $member->getRegistrationKey()) {
            $this->addTranslatedFlash('error', 'flash.profile.mail.already.confirmed');

            return $this->redirectToRoute('members_profile', ['username' => $username]);
        }

        // back to the page with the button: the setup for a new member, else the confirm page
        $back = MemberStatusType::AWAITING_MAIL_CONFIRMATION === $member->getStatus() ? 'finish_setup' : 'confirm_email';
        if (!$confirmationMailLimiter->create('member-' . $member->getId())->consume()->isAccepted()) {
            $this->addTranslatedFlash('error', 'flash.signup.resend.too.many');

            return $this->redirectToRoute($back, ['username' => $username]);
        }

        $mailer->sendSignupEmail($member, 'resent', [
            'subject' => $this->getTranslator()->trans('signup.confirm.email'),
            'username' => $username,
            'gender' => $member->getGender(),
            'email_address' => $member->getEmail(),
            'key' => $member->getRegistrationKey(),
        ]);
        $this->addTranslatedFlash('notice', 'flash.signup.resent', ['email' => $member->getEmail()]);

        return $this->redirectToRoute($back, ['username' => $username]);
    }

    /**
     * Part of signup controller as confirming email concerns signup logic. But the route points to 'members' as
     * handling is done when logged in.
     */
    #[Route(
        path: '/members/{username:member}/confirm/{registrationKey}',
        name: 'confirm_email',
        defaults: ['registrationKey' => null],
        priority: 20
    )]
    public function confirmEmailAddress(Request $request, Member $member, ?string $registrationKey): Response
    {
        $loggedInMember = $this->getUser();
        if ($member !== $loggedInMember) {
            $this->addTranslatedFlash('error', 'flash.signup.wrong.user');

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        if (null === $member->getRegistrationKey()) {
            $this->addTranslatedFlash('error', 'flash.profile.mail.already.confirmed');

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $confirmEmailAddressForm = $this->createForm(ConfirmEmailAddressFormType::class, [
            'registration_key' => $registrationKey,
        ]);
        $confirmEmailAddressForm->handleRequest($request);
        if ($confirmEmailAddressForm->isSubmitted() && $confirmEmailAddressForm->isValid()) {
            $registrationKey = $confirmEmailAddressForm['registration_key']->getData();
            if ($registrationKey === $member->getRegistrationKey()) {
                // Okay, email address confirmed. Check if account is already activated
                if (MemberStatusType::ACCOUNT_ACTIVATED === $member->getStatus()) {
                    $member->setStatus(MemberStatusType::ACTIVE);
                    $this->addTranslatedFlash('notice', 'flash.signup.complete');
                } else {
                    $member->setStatus(MemberStatusType::MAIL_CONFIRMED);
                    $this->addTranslatedFlash('notice', 'flash.signup.mail.confirmed');
                }
                $member->setRegistrationKey(null);

                $this->entityManager->persist($member);
                $this->entityManager->flush();
                // The email address is confirmed: the signup funnel's goal in Plausible (#540)
                $this->addFlash('plausible_event', 'Signup Confirmed');

                if (MemberStatusType::ACTIVE === $member->getStatus()) {
                    return $this->redirectToRoute('profile_edit', ['username' => $member->getUsername()]);
                }

                return $this->redirectToRoute('finish_setup', ['username' => $member->getUsername()]);
            }

            // a wrong key: the form again with the error (it went on to the setup page without a word)
            $confirmEmailAddressForm->get('registration_key')->addError(new FormError($this->getTranslator()->trans('error.registration.key')));
        }

        return $this->render('signup/confirm.email.html.twig', [
            'hide_finish_setup' => true,
            'member' => $member,
            'confirm_email' => $confirmEmailAddressForm,
            // the box that sends the confirmation mail again (not on the page for a changed address)
            'resend' => true,
        ]);
    }

    private function setUsernameOrEmailNotUniqueError(\Symfony\Component\Form\FormInterface $signupForm): void
    {
        $notUniqueError = new FormError($this->getTranslator()->trans('signup.error.not.unique'));
        $signupForm->get('username')->addError($notUniqueError);
        $signupForm->get('email')->addError($notUniqueError);
    }
}
