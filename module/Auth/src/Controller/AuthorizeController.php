<?php

namespace Rox\Auth\Controller;

use LoginController as LegacyLoginController;
use stdClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class AuthorizeController
 */
class AuthorizeController
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var UrlGeneratorInterface
     */
    protected $urlGenerator;

    public function __construct(Session $session, UrlGeneratorInterface $urlGenerator)
    {
        $this->session = $session;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @param Request $request
     * @SuppressWarnings(PHPMD.Superglobals)
     * @return RedirectResponse
     */
    public function __invoke(Request $request)
    {
        // Use the old login controller to do the checks for now
        $loginController = new LegacyLoginController();

        $args = new stdClass();

        $args->post = [
            'u' => $request->request->get('username'),
            'p' => $request->request->get('password'),
            'r' => $request->request->get('remember'),
        ];

        $args->request = $request->getPathInfo();

        $flashMessage = new stdClass();

        // loginCallback can echo output - capture it and ignore
        ob_start();

        $result = $loginController->loginCallback($args, null, $flashMessage);

        ob_end_clean();

        if (!$result) {
            $this->session->getFlashBag()->set('username', $args->post['u']);
            $this->session->getFlashBag()->set('errors', strip_tags($flashMessage->errmsg));

            return new RedirectResponse($this->urlGenerator->generate('auth/login'));
        }

        // Take the resulting IdMember session value and put it in the native Symfony session.
        $this->session->set('IdMember', (int) $_SESSION['IdMember']);

        return new RedirectResponse($this->urlGenerator->generate('home'));
    }
}
