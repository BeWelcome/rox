<?php

namespace Rox\Login;

use Rox\Framework\TwigView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class LoginPage extends TwigView
{
    public function __construct(Router $router, Request $request) {
        parent::__construct($router, true, $request);
        $this->addLateJavascriptFile('login/login.js');
        $this->setTemplate('login.html.twig', 'login');
    }
}
