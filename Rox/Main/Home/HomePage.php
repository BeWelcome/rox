<?php

namespace Rox\Main\Home;

use Rox\Framework\TwigView;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;

class HomePage extends TwigView
{
    public function __construct(Router $router, Request $request) {
        parent::__construct($router, true, $request);
        $this->addLateJavascriptFile('home/home.js');
        $this->setTemplate('home.html.twig', 'home');
    }
}
