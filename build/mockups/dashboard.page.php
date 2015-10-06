<?php

class DashboardPage extends Rox\Framework\TwigView
{
    public function __construct() {
        parent::__construct(null);
        $this->addEarlyJavascriptFile('bootstrap-autohidingnavbar/jquery.bootstrap-autohidingnavbar.js');
        $this->setTemplate('dashboard.html.twig', 'dashboard', array('title' => 'Be Welcome'));
    }
}
