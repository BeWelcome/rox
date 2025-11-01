<?php

namespace App\Controller;

use App\Entity\NewMember as Member;
use App\Security\AccountBannedException;
use App\Security\AccountDeniedLoginException;
use App\Security\AccountMailConfirmedException;
use App\Security\AccountMailNotConfirmedException;
use App\Security\AccountSuspendedException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller used to manage the application security.
 */
class SecurityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route(path: '/login', name: 'login', defaults: ['access_denied_redirect' => '/'])]
    #[Route(path: '/login', name: 'security_login', defaults: ['access_denied_redirect' => '/'])]
    #[Route(path: '/login_check', name: 'security_check', defaults: ['access_denied_redirect' => '/'])]
    public function login(AuthenticationUtils $helper): Response
    {
        $user = $this->getUser();
        if (null !== $user) {
            return $this->redirectToRoute('homepage');
        }

        $lastUsername = $helper->getLastUsername();
        $error = $helper->getLastAuthenticationError();

        $content = $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
            ]
        );

        return $content;
    }
}
