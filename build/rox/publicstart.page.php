<?php

class PublicStartpage extends \Rox\Framework\TwigView
{
    public function __construct($router) {
        parent::__construct($router, false);
        $this->addStylesheet('start/parallax.css');
        $this->addEarlyJavascriptFile('bootstrap-autohidingnavbar/jquery.bootstrap-autohidingnavbar.js');
        $this->addEarlyJavascriptFile('start/start.js');
        $this->setTemplate('public.html.twig', 'start', array('title' => 'Be Welcome'));
    }
}
