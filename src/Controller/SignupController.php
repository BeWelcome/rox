<?php

namespace App\Controller;

use App\Doctrine\AccommodationType;
use App\Doctrine\MemberStatusType;
use App\Entity\NewMember as Member;
use App\Form\ConfirmEmailAddressFormType;
use App\Form\SignupFormFinalizeType;
use App\Form\SignupFormType;
use App\Model\SignupModel;
use App\Service\Mailer;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class SignupController extends AbstractController
{
    use TranslatedFlashTrait;
    use TranslatorTrait;

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route(path: '/signup', name: 'signup', methods: ['GET', 'POST'])]
    public function signup(
        Request $request,
        SignupModel $signupModel,
        Security $security,
        array $locales,
    ): Response {
        $signupFormData = ['locale' => $request->getLocale()];
        if ($request->isMethod('POST')) {
            $signupFormData['username'] = $request->get('username');
        }

        $loggedInMember = $this->getUser();

        if (null !== $loggedInMember) {
            return $this->redirectToRoute('homepage');
        }

        $signupForm = $this->createForm(SignupFormType::class, $signupFormData);
        $signupForm->handleRequest($request);

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
        ]);
    }

    #[Route(path: '/members/{username:member}/finalize', name: 'finish_setup')]
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
            $this->addTranslatedFlash('notice', 'signup.activate.revisit');

            return $this->redirectToRoute('resend_confirmation_email', ['username' => $member->getUsername()]);
        }

        $finalizeForm = $this->createForm(
            SignupFormFinalizeType::class,
            options: ['show_registration_key' => null !== $member->getRegistrationKey()]
        );
        $finalizeForm->handleRequest($request);

        if ($finalizeForm->isSubmitted() && $finalizeForm->isValid()) {
            if (
                AccommodationType::YES === $finalizeForm->get('accommodation')->getData()
                && '0' === $finalizeForm->get('hosting_interest')->getData()
            ) {
                $finalizeForm
                    ->get('hosting_interest')
                    ->addError(new FormError($this->translator->trans('error.hosting.interest')))
                ;
            } else {
                $signupModel->updateMember($member, $finalizeForm->getData());
                $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $member->getUsername());

                return $this->redirectToRoute('editmyprofile');
            }
        }

        return $this->render('signup/finalize.html.twig', [
            'hide_finish_setup' => true,
            'member' => $member,
            'finalize' => $finalizeForm->createView(),
        ]);
    }

    #[Route(path: '/members/{username:member}/resend', name: 'resend_confirmation_email')]
    public function resendConfirmationEmail(
        Member $member,
        AuthenticationUtils $helper,
        Mailer $mailer,
        EntityManagerInterface $entityManager,
    ): Response {
        $username = $member->getUsername();
        if ($helper->getLastUsername() !== $username) {
            throw $this->createAccessDeniedException();
        }

        $memberRepository = $entityManager->getRepository(Member::class);
        /** @var Member $member */
        $member = $memberRepository->findOneBy(['username' => $username]);
        if (!$member) {
            throw $this->createAccessDeniedException();
        }

        $subject = $this->getTranslator()->trans('signup.confirm.email');
        $parameters = [
            'subject' => $subject,
            'username' => $username,
            'gender' => $member->getGender(),
            'email_address' => $member->getEmail(),
            'key' => $member->getRegistrationKey(),
        ];

        $mailer->sendSignupEmail(
            $member,
            'resent',
            $parameters
        );

        return $this->render('signup/resent.html.twig', $parameters);
    }

    /**
     * Part of signup controller as confirming email concerns signup logic. But the route points to 'members' as
     * handling is done when logged in.
     */
    #[Route(path: '/members/{username:member}/confirm', name: 'confirm_email')]
    public function confirmEmailAddress(Request $request, Member $member): Response
    {
        $loggedInMember = $this->getUser();
        if ($member !== $loggedInMember) {
            $this->addTranslatedFlash('error', 'flash.signup.wrong.user');

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $confirmEmailAddressForm = $this->createForm(ConfirmEmailAddressFormType::class);
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
            }

            if (MemberStatusType::ACTIVE === $member->getStatus()) {
                return $this->redirectToRoute('editmyprofile');
            }
        }

        return $this->render('signup/confirm.email.html.twig', [
            'hide_finish_setup' => true,
            'member' => $member,
            'confirm_email' => $confirmEmailAddressForm->createView(),
        ]);
    }

    private function setUsernameOrEmailNotUniqueError(\Symfony\Component\Form\FormInterface $signupForm): void
    {
        $notUniqueError = new FormError($this->getTranslator()->trans('signup.error.not.unique'));
        $signupForm->get('username')->addError($notUniqueError);
        $signupForm->get('email')->addError($notUniqueError);
    }
}
