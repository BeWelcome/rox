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

    public function success($data) {
        $this->response('success', $data);
    }

    public function error($message) {
        $data = new stdClass;
        $data->errorMessage = $message;
        $this->response('error', $data);
    }

    public function response($resultType, $data) {
        $result = (object) array('result' => $resultType);
        $content = (object) array_merge((array) $result, (array) $data);
        $callback = $this->_getCallback();
        $this->_view->response($content, $callback);
    }

    public function index() {
        $this->error('Does not compute');
    }

    public function memberAction() {
        $username = $this->route_vars['username'];
        $member = $this->_model->getMember($username);
        if ($member == false) {
            $this->error('Member not found');
        } else {
            if ($member->isPublic()) {
                $memberData = $this->_model->getMemberData($member);
                $this->success($memberData);
            } else {
                $this->error('Profile not public');
            }
        }
    }

    private function _getCallback() {
        if (isset($this->args_vars->get['callback'])) {
            return $this->args_vars->get['callback'];
        } else {
            return false;
        }
    }
}

?>