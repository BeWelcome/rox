<?php

namespace Rox\Auth\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class LoginController.
 */
class LoginController
{
    /**
     * @var EngineInterface
     */
    protected $engine;

    /**
     * @var AuthenticationUtils
     */
    protected $authenticationUtils;

    public function __construct(EngineInterface $engine, AuthenticationUtils $authenticationUtils)
    {
        $this->engine = $engine;
        $this->authenticationUtils = $authenticationUtils;
    }

    /**
     * @return Response
     */
    public function loginFormAction()
    {
        $lastError = $this->authenticationUtils->getLastAuthenticationError();

        $content = $this->engine->render('@auth/login.html.twig', [
            'username' => $this->authenticationUtils->getLastUsername(),
            'error' => $lastError ? $lastError->getMessage() : null,
        ]);

        return new Response($content);
    }
}
