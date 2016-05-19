<?php

namespace Rox\Main\Start;

use Symfony\Component\Routing\Router;

class StartPage extends \Rox\Framework\TwigView
{
    public function __construct(Router $router) {
        parent::__construct($router, false);
        $this->addStylesheet('start/parallax.css');
        $this->addEarlyJavascriptFile('tether-1.1.1/js/tether.min.js');
        $this->addEarlyJavascriptFile('bootstrap/bootstrap.js');
        $this->addEarlyJavascriptFile('start/skrollr.js');
        $this->addEarlyJavascriptFile('start/skrollr.menu.js');
        $this->addEarlyJavascriptFile('start/jquery.anyslider.js');
        $this->addEarlyJavascriptFile('start/start.js');
        $this->setTemplate('public.html.twig', 'start', array('title' => 'BeWelcome'));
    }
}
