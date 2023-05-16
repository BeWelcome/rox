<?php

namespace App\Controller;

use App\Entity\Member;
use App\Entity\PasswordReset;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Model\PasswordModel;
use App\Repository\MemberRepository;
use App\Service\Mailer;
use App\Utilities\ManagerTrait;
use App\Utilities\ProfileSubmenu;
use App\Utilities\TranslatedFlashTrait;
use App\Utilities\TranslatorTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PasswordController extends AbstractController
{
    use ManagerTrait;
    use TranslatedFlashTrait;
    use TranslatorTrait;

    private PasswordModel $passwordModel;
    private EntityManagerInterface $entityManager;

    public function __construct(PasswordModel $passwordModel, EntityManagerInterface $entityManager)
    {
        $this->passwordModel = $passwordModel;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/resetpassword", name="member_request_reset_password")
     */
    public function requestResetPassword(Request $request, Mailer $mailer): Response
    {
        // Someone obviously lost their way. No sense in resetting your password if you're currently logged in.
        if ($this->isGranted('ROLE_USER')) {
            $this->addTranslatedFlash('notice', 'flash.reset.password.not_logged_in');

            return $this->redirectToRoute('landingpage');
        }

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            /** @var MemberRepository $memberRepository */
            $memberRepository = $this->entityManager->getRepository(Member::class);
            try {
                /** @var Member $member */
                $member = $memberRepository->loadUserByIdentifier($data['username']);
            } catch (NonUniqueResultException $e) {
                $member = null;
            }
            if (null === $member) {
                $form->addError(new FormError($this->getTranslator()->trans('resetpassworderror')));
            } else {
                try {
                    $token = $this->passwordModel->generatePasswordResetToken($member);
                } catch (Exception $e) {
                    $token = null;
                }
                if (null === $token) {
                    $this->addTranslatedFlash('error', 'flash.no.reset.password');

                    return $this->redirectToRoute('security_login');
                }

                /* Sent the member a link to follow to reset the password */
                $sent = $mailer->sendPasswordResetLinkEmail(
                    $member,
                    [
                        'receiver' => $member,
                        'subject' => 'reset.password.subject',
                        'token' => $token,
                    ]
                );
                if ($sent) {
                    $this->addTranslatedFlash('notice', 'flash.email.reset.password');

                    return $this->redirectToRoute('security_login');
                }
                $form->addError(new FormError('There was an error sending the password reset link.'));
            }
        }

        return $this->render('member/request.password.reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/resetpassword/{username}/{token}", name="member_reset_password",
     *     requirements={"token": "[a-z0-9]{64}"})
     */
    public function resetPassword(
        Request $request,
        Member $member,
        string $token,
        PasswordModel $passwordModel
    ): Response {
        // Someone obviously lost their way. No sense in resetting your password if you're currently logged in.
        if ($this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('landingpage');
        }

        $repository = $this->entityManager->getRepository(PasswordReset::class);
        /** @var PasswordReset $passwordReset */
        $passwordReset = $repository->findOneBy(['member' => $member, 'token' => $token]);

        if (null === $passwordReset) {
            $this->addTranslatedFlash('error', 'flash.reset.password.invalid');

            return $this->redirectToRoute('member_request_reset_password');
        }

        $diffInDays = $passwordReset->getGenerated()->diffInDays();
        if ($diffInDays > 2) {
            $this->addFlash('error', 'flash.reset.password.invalid');

            return $this->redirectToRoute('member_request_reset_password');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newPassword = $passwordModel->getPasswordHash($member, $data['password']);
            $member->setPassword($newPassword);
            $this->entityManager->persist($member);
            $this->entityManager->flush();

            $this->passwordModel->removePasswordResetTokens($member);
            $this->addTranslatedFlash('notice', 'flash.password.reset');

            return $this->redirectToRoute('security_login');
        }

        return $this->render('member/reset.password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/members/{username}/password/change", name="change_password",
     *     requirements={"token": "[a-z0-9]{64}"})
     */
    public function changePassword(
        Request $request,
        Member $member,
        ProfileSubmenu $profileSubmenu,
        PasswordModel $passwordModel
    ): Response {
        /** @var Member $loggedInMember */
        $loggedInMember = $this->getUser();
        if ($member !== $loggedInMember) {
            return $this->redirectToRoute('members_profile', ['username' => $member->getUsername()]);
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $currentPassword = $data['current'];
            if ($passwordModel->checkPassword($member, $currentPassword)) {
                $hashedPassword = $passwordModel->getPasswordHash($member, $data['password']);
                $member->setPassword($hashedPassword);

                $this->entityManager->persist($member);
                $this->entityManager->flush();

                return $this->redirectToRoute('security_logout');
            }
        }

        return $this->render('profile/change.password.html.twig', [
            'member' => $member,
            'form' => $form->createView(),
            'submenu' => $profileSubmenu->getSubmenu($member, $loggedInMember),
        ]);
    }
}
