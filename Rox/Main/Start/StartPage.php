<?php

namespace Rox\Main\Start;

use Symfony\Component\Routing\Router;

class StartPage extends \Rox\Framework\TwigView
{
    public function __construct(Router $router) {
        parent::__construct($router, false, false);
        $this->addStylesheet('start/parallax.css');
        $this->addEarlyJavascriptFile('start/start.js');
        $this->addEarlyJavascriptFile('start/skrollr.js');
        $this->addEarlyJavascriptFile('start/skrollr.menu.js');
        $this->setTemplate('public.html.twig', 'start', array('title' => 'BeWelcome'));
    }
}
