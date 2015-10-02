<?php

class Select2Page extends TwigView
{
    public function __construct() {
        parent::__construct(false);
        $this->addEarlyJavascriptFile('bootstrap-autohidingnavbar/jquery.bootstrap-autohidingnavbar.js');
        $this->addLateJavascriptFile('start/select.js');

        $this->setTemplate('select2.html.twig', 'dashboard', array('title' => 'Be Welcome'));
    }
}
