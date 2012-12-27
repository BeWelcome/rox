<?php

class ApiView extends RoxAppView
{
    private $_model;

    public function __construct(ApiModel $model) {
        $this->_model = $model;
    }

    public function error($message) {
        $content = array('result' => 'error', 'message' => $message);
        $this->jsonResponse($content);
    }

    public function jsonResponse($content) {
        header('Content-type: application/json');
        echo json_encode($content);
    }
}

?>