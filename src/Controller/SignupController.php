<?php

namespace App\Controller;

use App\Doctrine\AccommodationType;
use App\Doctrine\MemberStatusType;
use App\Entity\Member;
use App\Entity\MemberPreference;
use App\Entity\Preference;
use App\Form\SignupFormFinalizeType;
use App\Form\SignupFormType;
use App\Model\SignupModel;
use App\Repository\MemberRepository;
use App\Service\Mailer;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Contracts\Translation\TranslatorInterface;

class SignupController extends AbstractController
{
    use TranslatorTrait;
    use TranslatedFlashTrait;

    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/signup", name="signup", methods={"GET", "POST"})")
     */
    public function signup(
        Request $request,
        SignupModel $signupModel,
        TranslatorInterface $translator,
        array $locales
    ): Response {
        $signupFormData = [];
        if ($request->isMethod("POST")) {
            $signupFormData['username'] = $request->get("username");
        }

        $signupForm = $this->createForm(SignupFormType::class, $signupFormData);
        $signupForm->handleRequest($request);

        if ($signupForm->isSubmitted() && $signupForm->isValid()) {
            $signupData = $signupForm->getData();
            if (!$signupModel->checkUsername($signupData['username'])) {
                $signupForm->get('username')->addError(
                    new FormError($translator->trans('signup.username.error.not.unique'))
                );
            }
            if (!$signupModel->checkEmailAddress($signupData['email'])) {
                $signupForm->get('email')->addError(new FormError($translator->trans('signup.email.error.not.unique')));
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

    /**
     * @Route("/signup/{username}/finalize", name="signup_finalize")
     */
    public function signupFinalize(
        Request $request,
        Member $member,
        SignupModel $signupModel
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();

        if (null !== $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $loggedInMember->getUsername()]);
        }

        if (
            !in_array(
                $member->getStatus(),
                [MemberStatusType::AWAITING_MAIL_CONFIRMATION, MemberStatusType::MAIL_CONFIRMED]
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
                && "0" === $finalizeForm->get('hosting_interest')->getData()
            ) {
                $finalizeForm
                    ->get('hosting_interest')
                    ->addError(new FormError($this->translator->trans('error.hosting.interest')))
                ;
            } else {
                $signupModel->updateMember($member, $finalizeForm->getData());
                $request->getSession()->set(Security::LAST_USERNAME, $member->getUsername());
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

    /**
     * @Route("/signup/finish", name="signup_finish")
     */
    public function finishSignup(Request $request, Mailer $mailer): Response
    {
        $signupVars = $request->getSession()->get('SignupBWVars');

        if (!empty($signupVars)) {
            $email = $signupVars['email'];
            $username = strtolower($signupVars['username']);
            $key = hash('sha256', strtolower($email) . ' - ' . strtolower($username));

            // Member isn't logged in at this time, so we need to find it in the database.
            $memberRepository = $this->getDoctrine()->getRepository(Member::class);
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

    /**
     * @Route("/signup/resend/{username}", name="resend_confirmation_email")
     */
    public function resendConfirmationEmail(Member $member, AuthenticationUtils $helper, Mailer $mailer): Response
    {
        $username = $member->getUsername();
        if ($helper->getLastUsername() !== $username) {
            throw $this->createAccessDeniedException();
        }

        $memberRepository = $this->getDoctrine()->getRepository(Member::class);
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
     * @Route("/signup/confirm/{username}/{registrationKey}", name="signup_confirm")
     */
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
                $request->getSession()->set(Security::LAST_USERNAME, $username);
                $this->addTranslatedFlash('notice', 'flash.signup.activated');
            }

            return $this->redirectToRoute('editmyprofile');
        }

        $this->addTranslatedFlash('error', 'flash.signup.key.invalid');

        return $this->redirectToRoute('security_login');
    }
}
