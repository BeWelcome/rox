<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Member;
use App\Form\AccountEditFormType;
use App\Model\ProfileModel;
use App\Utilities\ChangeProfilePictureGlobals;
use App\Utilities\ProfileSubmenu;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
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
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        return $this->render('profile/account.edit.html.twig', [
            'form' => $form,
            'member' => $member,
            'status_form' => $profileModel->getStatusForm($loggedInMember, $member),
            'globals_js_json' => $globals->getGlobalsJsAsJson($loggedInMember, $member),
            'submenu' => $profileSubmenu->getSubmenu($loggedInMember, $member),
        ]);
    }
}
