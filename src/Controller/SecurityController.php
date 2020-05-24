<?php

namespace App\Controller;

use App\Security\AccountBannedException;
use App\Security\AccountDeniedLoginException;
use App\Security\AccountMailNotConfirmedException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Controller used to manage the application security.
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login", defaults={"access_denied_redirect" = "/"}))
     * @Route("/login", name="security_login", defaults={"access_denied_redirect" = "/"}))
     * @Route("/login_check", name="security_check", defaults={"access_denied_redirect" = "/"}))
     *
     * @param AuthenticationUtils $helper
     *
     * @return Response
     */
    public function loginAction(AuthenticationUtils $helper)
    {
        $user = $this->getUser();
        if ($user) {
            return $this->redirectToRoute('homepage');
        }

        $error = $helper->getLastAuthenticationError();
        $lastUsername = $helper->getLastUsername();

        $showInvalidCredentialsHint = false;
        $showResendConfirmationLink = false;
        $showBannedHint = false;
        $showExpiredHint = false;
        $showNotAllowedToLogin = false;
        if (is_object($error)) {
            switch(get_class($error))
            {
                case AccountMailNotConfirmedException::class:
                    $showResendConfirmationLink = ($lastUsername) ? true : false;
                    break;
                case BadCredentialsException::class:
                    $showInvalidCredentialsHint = true;
                    break;
                case AccountBannedException::class:
                    $showBannedHint = true;
                    break;
                case AccountDeniedLoginException::class:
                    $showNotAllowedToLogin = true;
                    break;
                case AccountExpiredException::class:
                    $showExpiredHint = true;
                    break;
                default:
                    ;
            }
        }

        $content = $this->render(
            'security/login.html.twig',
            [
                'last_username' => $lastUsername,
                'error' => $error,
                'resend_confirmation' => $showResendConfirmationLink,
                'invalid_credentials' => $showInvalidCredentialsHint,
                'member_banned' => $showBannedHint,
                'member_expired' => $showExpiredHint,
                'member_not_allowed_to_login' => $showNotAllowedToLogin,
            ]
        );

        return $content;
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in app/config/security.yml
     *
     * @Route("/logout", name="security_logout")
     *
     * @throws Exception
     */
    public function logoutAction()
    {
        throw new Exception('This should never be reached!');
    }
}
