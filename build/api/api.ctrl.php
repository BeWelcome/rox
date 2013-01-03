<?php

class ApiController extends RoxControllerBase
{
    private $_model;
    private $_view;

    public $supporedFormats = array('json', 'js');

    public function __construct() {
        parent::__construct();
        $this->_model = new ApiModel();
        $this->_view  = new ApiView($this->_model);
    }

    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }

    public function checkFormat() {
        $format = $this->route_vars['format'];
        if (in_array($format, $this->supporedFormats) == false) {
            $this->_view->rawResponse('Invalid request: Format "' . $format
                . '" not supported');
        }
        $callback = $this->_getCallback();
        if ($format == 'js' && $callback == false) {
            $this->_view->rawResponse(
                'Invalid request: JSONP callback missing');
        }
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
        $this->_view->rawResponse('Does not compute');
    }

    public function memberAction() {
        $this->checkFormat();
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
        if (isset($this->args_vars->get['callback']) &&
            $this->args_vars->get['callback'] != '') {
            return $this->args_vars->get['callback'];
        } else {
            return false;
        }
    }
}

?>