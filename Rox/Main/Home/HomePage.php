<?php

namespace Rox\Main\Home;

use Symfony\Component\Routing\Router;

class HomePage extends \Rox\Framework\TwigView
{
    public function __construct(Router $router) {
        parent::__construct($router);
        $this->addEarlyJavascriptFile('bootstrap-autohidingnavbar/jquery.bootstrap-autohidingnavbar.js');
        $this->setTemplate('home.html.twig', 'home', array('title' => 'BeWelcome'));
    }
}
