<?php

namespace Rox\Login;

use Rox\Framework\TwigView;
use Symfony\Component\Routing\Router;

class LoginPage extends TwigView
{
    public function __construct(Router $router) {
        parent::__construct($router);
        $this->addLateJavascriptFile('login/login.js');
        $this->setTemplate('login.html.twig', 'login');
    }
}
