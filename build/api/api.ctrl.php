<?php

class ApiController extends RoxControllerBase
{
    private $_model;
    private $_view;

    public function __construct() {
        parent::__construct();
        $this->_model = new ApiModel();
        $this->_view  = new ApiView($this->_model);
    }

    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }

    public function index() {
        $this->_view->error('Does not compute');
        exit;
    }

    public function memberAction() {
        $username = $this->route_vars['username'];
        $member = $this->_model->getMember($username);
        if ($member == false) {
            $this->_view->error('Member not found');
        } else {
            if ($member->isPublic()) {
                $memberData = $this->_model->getMemberData($member);
                $this->_view->jsonResponse($memberData);
            } else {
                $this->_view->error('Profile not public');
            }
        }
        exit;
    }
}

?>