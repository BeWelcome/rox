<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\AccountEditFormType;
use App\Form\ConfirmEmailAddressFormType;
use App\Model\ProfileModel;
use App\Model\SignupModel;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
        $loggedInMember = $this->getUser();
        if ($loggedInMember !== $member) {
            return $this->redirectToRoute('account_edit', ['username' => $member->getUsername()]);
        }

        $form = $this->createForm(AccountEditFormType::class, [
            'name' => $member->getName(),
            'show_name' => $member->isNameVisible(),
            'short_name' => $member->getUsername(),
            'gender' => $member->getGender(),
            'show_gender' => $member->isGenderVisible(),
            'birthdate' => $member->getBirthdate(),
            'show_age' => $member->isAgeVisible(),
            'email' => $member->getEmail(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // Check if email was changed, and the new one is unique
            $email = $data['email'];

            if ($email !== $member->getEmail()) {
                $unknownEmail = $this->signupModel->checkEmailAddress($email);
                if (!$unknownEmail) {
                    $form->get('email')->addError(new FormError($this->translator->trans('account.new.email.taken')));
                }
            }

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

                if ($data['email'] !== $member->getEmail()) {
                    $member->setNewEmail($data['email']);
                    $this->profileModel->sendEmailConfirmationEmail($member, $data['email']);
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

        $changeEmailAddressForm = $this->createForm(ConfirmEmailAddressFormType::class, [
            'registration_key' => $registrationKey,
        ]);
        $changeEmailAddressForm->handleRequest($request);
        if ($changeEmailAddressForm->isSubmitted() && $changeEmailAddressForm->isValid()) {
            $registrationKey = $changeEmailAddressForm['registration_key']->getData();
            if ($registrationKey === $member->getRegistrationKey()) {
                $member->setEmail($member->getNewEmail());
                $member->setNewEmail(null);

                $member->setRegistrationKey(null);

                $this->entityManager->persist($member);
                $this->entityManager->flush();
            }

            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        return $this->render('signup/confirm.email.html.twig', [
            'hide_finish_setup' => true,
            'member' => $member,
            'confirm_email' => $changeEmailAddressForm,
        ]);
    }
}
