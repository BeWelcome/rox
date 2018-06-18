<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Controller used to manage the application security.
 */
class SecurityController extends Controller
{
    /**
     * @Route("/login", name="security_login", defaults={"access_denied_redirect" = "/"}))
     * @Route("/login_check", name="security_check", defaults={"access_denied_redirect" = "/"}))
     */
    public function loginAction()
    {
        $helper = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $helper->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $helper->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * This is the route the user can use to logout.
     *
     * But, this will never be executed. Symfony will intercept this first
     * and handle the logout automatically. See logout in app/config/security.yml
     *
     * @Route("/logout", name="security_logout")
     * @throws \Exception
     */
    public function logoutAction()
    {
        throw new \Exception('This should never be reached!');
    }
}
