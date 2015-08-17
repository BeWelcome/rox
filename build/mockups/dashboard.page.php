<?php

class DashboardPage extends TwigView
{
    public function __construct() {
        parent::__construct(false);
        $this->addEarlyJavascriptFile('bootstrap-autohidingnavbar/jquery.bootstrap-autohidingnavbar.js');
        $this->setTemplate('dashboard.html.twig', 'dashboard', array('title' => 'Be Welcome'));
    }
}
