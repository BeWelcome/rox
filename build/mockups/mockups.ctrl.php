<?php

/**
* dashboard controller
*
* @package Dashboard
* @author Amnesiac84
*/
class MockupsController extends RoxControllerBase
{

    /**
     * @RoxModelBase Rox
     */
    private $_model;

// for some things we still need a class-scope view object
    private $_view;


    /**
     * @see /build/mytravelbook/mytravelbook.ctrl.php
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->_model = new RoxModelBase();
        $this->_view = new RoxView($this->_model);
    }

    public function __destruct()
    {
        unset($this->_model);
        unset($this->_view);
    }

    public function dashboard() {
        $page = new DashboardPage();
        return $page;
    }
}