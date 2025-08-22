<?php

namespace App\Controller;

use App\Doctrine\AccommodationType;
use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Form\SignupFormFinalizeType;
use App\Form\SignupFormType;
use App\Model\SignupModel;
use App\Service\Mailer;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        array $locales,
    ): Response {
        $signupFormData = [];
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
                $locale = $request->getPreferredLanguage($locales);
                $member = $signupModel->createAccount($signupData, $locale);

                return $this->redirectToRoute('signup_finalize', [
                    'username' => $member->getUsername(),
                ]);
            }
        }

        return $this->render('signup/first.step.html.twig', [
            'signup' => $signupForm->createView(),
        ]);
    }

    #[Route(path: '/signup/finalize/{username}', name: 'signup_finalize')]
    public function signupFinalize(
        Request $request,
        Member $member,
        SignupModel $signupModel,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if (null !== $loggedInMember) {
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

        $finalizeForm = $this->createForm(SignupFormFinalizeType::class);
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

        if (MemberStatusType::AWAITING_MAIL_CONFIRMATION === $member->getStatus()) {
            $this->addTranslatedFlash('notice', 'profile.mail.not.confirmed');
        }

        return $this->render('signup/finalize.html.twig', [
            'member' => $member,
            'finalize' => $finalizeForm->createView(),
        ]);
    }

    #[Route(path: '/signup/finish', name: 'signup_finish')]
    public function finishSignup(Request $request, Mailer $mailer, EntityManagerInterface $entityManager): Response
    {
        $signupVars = $request->getSession()->get('SignupBWVars');

        if (!empty($signupVars)) {
            $email = $signupVars['email'];
            $username = strtolower((string) $signupVars['username']);
            $key = hash('sha256', strtolower((string) $email) . ' - ' . strtolower($username));

            // Member isn't logged in at this time, so we need to find it in the database.
            $memberRepository = $entityManager->getRepository(Member::class);
            /** @var Member $member */
            $member = $memberRepository->findOneBy(['username' => $username]);
            if (!$member) {
                throw new Exception('No member found in database. Terminating.');
            }

            $member->setRegistrationKey($key);
            $em = $this->entityManager;
            $em->persist($member);
            $em->flush();

            $subject = $this->getTranslator()->trans('signup.confirm.email');
            $parameters = [
                'subject' => $subject,
                'username' => $username,
                'email_address' => $email,
                'key' => $key,
            ];

            $mailer->sendSignupEmail(
                $member,
                'signup',
                $parameters
            );

            // Remove the session variable
            $request->getSession()->remove('SignupBWVars');

            // show finish page
            return $this->render('signup/finish.html.twig', $parameters);
        }

        return $this->render('signup/error.html.twig');
    }

    #[Route(path: '/signup/resend/{username:member}', name: 'resend_confirmation_email')]
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

    #[Route(path: '/signup/confirm/{username}/{registrationKey}', name: 'signup_confirm')]
    public function confirmEmailAddress(Request $request, string $username, string $registrationKey): Response
    {
        $loggedInMember = $this->getUser();
        $memberRepository = $this->entityManager->getRepository(Member::class);
        $member = $memberRepository->findOneBy(['username' => $username]);
        if (null === $member) {
            $this->addTranslatedFlash('error', 'flash.signup.username.invalid');

            return $this->redirectToRoute('security_login');
        }

        if (null !== $loggedInMember && $member !== $loggedInMember) {
            $this->addTranslatedFlash('error', 'flash.signup.wrong.user');

            return $this->redirectToRoute('members_profile', ['username' => $username]);
        }

        if ($registrationKey === $member->getRegistrationKey()) {
            // Okay, email address confirmed. Check if account is already activated
            if (null === $member->getAccommodation()) {
                $member->setStatus(MemberStatusType::MAIL_CONFIRMED);
                $this->addTranslatedFlash('notice', 'flash.signup.mail.confirmed');
            } else {
                $member->setStatus(MemberStatusType::ACTIVE);
                $this->addTranslatedFlash('notice', 'flash.signup.active');
            }
            $member->setRegistrationKey(null);

            $this->entityManager->persist($member);
            $this->entityManager->flush();

            if (null === $member->getAccommodation()) {
                return $this->redirectToRoute('signup_finalize', ['username' => $member->getUsername()]);
            }

            if (null === $loggedInMember) {
                $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $username);
                $this->addTranslatedFlash('notice', 'flash.signup.activated');
            }

            return $this->redirectToRoute('editmyprofile');
        }

        $this->addTranslatedFlash('error', 'flash.signup.key.invalid');

        return $this->redirectToRoute('security_login');
    }

    private function setUsernameOrEmailNotUniqueError(\Symfony\Component\Form\FormInterface $signupForm): void
    {
        $notUniqueError = new FormError($this->getTranslator()->trans('signup.error.not.unique'));
        $signupForm->get('username')->addError($notUniqueError);
        $signupForm->get('email')->addError($notUniqueError);
    }
}
