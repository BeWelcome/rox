<?php

namespace Rox\Main\Home;

use Rox\Framework\TwigView;
use Symfony\Component\Routing\Router;

class HomePage extends TwigView
{
    public function __construct(Router $router) {
        parent::__construct($router);
        $this->addLateJavascriptFile('home/home.js');
        $this->setTemplate('home.html.twig', 'home');
    }
}
