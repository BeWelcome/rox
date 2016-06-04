<?php

namespace Rox\Auth\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class LoginController
 */
class LoginController
{
    /**
     * @var Session
     */
    protected $session;

    /**
     * @var EngineInterface
     */
    protected $engine;

    public function __construct(Session $session, EngineInterface $engine)
    {
        $this->session = $session;
        $this->engine = $engine;
    }

    /**
     * @return Response
     */
    public function __invoke()
    {
        $content = $this->engine->render('@auth/login.html.twig', [
            'flash' => $this->session->getFlashBag(),
        ]);

        return new Response($content);
    }

    public function logout()
    {
        $loginModel = new \LoginModel();

        $loginModel->logout();

        return new RedirectResponse('/');
    }
}
