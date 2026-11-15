<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\AccountEditFormType;
use App\Form\ChangeEmailFormType;
use App\Form\ConfirmEmailAddressFormType;
use App\Model\ProfileModel;
use App\Model\SignupModel;
use App\Security\MemberVoter;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class AccountController extends AbstractController
{
    use TranslatedFlashTrait;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ProfileModel $profileModel,
        private readonly SignupModel $signupModel,
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[Route(path: '/members/{username:member}/account', name: 'account_edit')]
    public function editAccountInfo(
        Request $request,
        Member $member,
        ChangeProfilePictureGlobals $globals,
        ProfileModel $profileModel,
        ProfileSubmenu $profileSubmenu,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        // another member's account page: to the own one (it was the same page again, a redirect loop); admins and the
        // safety team edit the member's account too (MemberVoter::MEMBER_EDIT), the email address included
        if (!$this->isGranted(MemberVoter::MEMBER_EDIT, $member)) {
            return $this->redirectToRoute('account_edit', ['username' => $loggedInMember->getUsername()]);
        }

        $form = $this->createForm(AccountEditFormType::class, [
            'name' => $member->getName(),
            'show_name' => $member->isNameVisible(),
            'short_name' => $member->getShortName(),
            'gender' => $member->getGender(),
            'show_gender' => $member->isGenderVisible(),
            'birthdate' => $member->getBirthdate(),
            'show_age' => $member->isAgeVisible(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            if (0 === $form->getErrors(true)->count()) {
                $member->setName($data['name']);
                $member->setShortName($data['short_name']);
                $member->setGender($data['gender']);
                $member->setBirthdate($data['birthdate']);

                $showAge = $data['show_age'] ?? false;
                if ($showAge) {
                    $member->showAge();
                } else {
                    $member->hideAge();
                }

                $showGender = $data['show_gender'] ?? false;
                if ($showGender) {
                    $member->showGender();
                } else {
                    $member->hideGender();
                }

                $showName = $data['show_name'] ?? false;
                if ($showName) {
                    $member->showName();
                } else {
                    $member->hideName();
                }

                $this->entityManager->persist($member);
                $this->entityManager->flush();

                return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
            }
        }

        return $this->render('profile/account.edit.html.twig', [
            'form' => $form,
            'member' => $member,
            'status_form' => $profileModel->getStatusForm($loggedInMember, $member),
            'globals_js_json' => $globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $profileSubmenu->getSubmenu($loggedInMember, $member),
        ]);
    }

    /**
     * A new email address, on a page of its own (linked from the account page): the member gives their password, admins
     * and the safety team change it without (MemberVoter::MEMBER_EDIT). Either way the address waits for the member to
     * confirm it: a mail to it with the link (changeEmailAddress), the current one stays until then.
     */
    #[Route(path: '/members/{username:member}/account/email', name: 'account_email', priority: 1)]
    public function changeEmailAddressRequest(
        Request $request,
        Member $member,
        PasswordHasherFactoryInterface $passwordHasherFactory,
        ChangeProfilePictureGlobals $globals,
        ProfileSubmenu $profileSubmenu,
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if (!$this->isGranted(MemberVoter::MEMBER_EDIT, $member)) {
            return $this->redirectToRoute('account_email', ['username' => $loggedInMember->getUsername()]);
        }

        $own = $member === $loggedInMember;
        $form = $this->createForm(ChangeEmailFormType::class, null, ['ask_password' => $own]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = (string) $form->get('email')->getData();
            if ($own && !$passwordHasherFactory->getPasswordHasher($member)->verify($member->getPassword(), (string) $form->get('password')->getData())) {
                $form->get('password')->addError(new FormError($this->translator->trans('form.error.password.incorrect')));
            }
            if ($member->hasUnconfirmedEmail()) {
                $form->get('email')->addError(new FormError($this->translator->trans('member.mail.not.confirmed')));
            } elseif ($email === $member->getEmail() || !$this->signupModel->checkEmailAddress($email)) {
                $form->get('email')->addError(new FormError($this->translator->trans('account.new.email.taken')));
            }

            if (0 === $form->getErrors(true)->count()) {
                $member->setNewEmail($email);
                $this->profileModel->sendEmailConfirmationEmail($member, $email);
                $this->entityManager->flush();
                $this->addTranslatedFlash('notice', 'flash.account.email.sent', ['email' => $email]);

                return $this->redirectToRoute('account_edit', ['username' => $member->getUsername()]);
            }
        }

        return $this->render('profile/account.email.html.twig', [
            'form' => $form,
            'member' => $member,
            'own' => $own,
            'globals_js_json' => $globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $profileSubmenu->getSubmenu($loggedInMember, $member),
        ]);
    }

    /**
     * Email change is triggered through editing your account therefore handling the process here.
     */
    #[Route(
        path: '/members/{username:member}/change/email/{registrationKey}',
        name: 'change_email',
        defaults: ['registrationKey' => null],
        priority: 20
    )]
    public function changeEmailAddress(Request $request, Member $member, ?string $registrationKey): Response
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

        $newEmail = $member->getNewEmail();
        if (null === $newEmail) {
            $this->addTranslatedFlash('error', 'flash.profile.mail.already.confirmed');

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $changeEmailAddressForm = $this->createForm(ConfirmEmailAddressFormType::class, [
            'registration_key' => $registrationKey,
        ]);
        $changeEmailAddressForm->handleRequest($request);
        if ($changeEmailAddressForm->isSubmitted() && $changeEmailAddressForm->isValid()) {
            $registrationKey = $changeEmailAddressForm['registration_key']->getData();
            if ($registrationKey === $member->getRegistrationKey()) {
                $member->setEmail($newEmail);
                $member->setNewEmail(null);

                $member->setRegistrationKey(null);

                $this->entityManager->persist($member);
                $this->entityManager->flush();

                return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
            }

            // a wrong key: the form again with the error (it went to the profile without a word)
            $changeEmailAddressForm->get('registration_key')->addError(new FormError($this->translator->trans('error.registration.key')));
        }

        return $this->render('signup/confirm.email.html.twig', [
            'hide_finish_setup' => true,
            'member' => $member,
            'confirm_email' => $changeEmailAddressForm,
        ]);
    }
}
