<?php

class ApiView extends RoxAppView
{
    private $_model;

    public function __construct(ApiModel $model) {
        $this->_model = $model;
    }

    public function error($message, $callback = false) {
        $content = array('result' => 'error', 'message' => $message);
        $this->response($content, $callback);
    }

    public function response($content, $callback = false) {
        if ($callback) {
            $this->jsonpResponse($content, $callback);
        } else {
            $this->jsonResponse($content);
        }
    }

    public function jsonResponse($content) {
        header('Content-type: application/json');
        echo json_encode($content);
    }

    public function jsonpResponse($content, $callback) {
        header('Content-type: application/javascript');
        $javascript = $callback . '(' . json_encode($content) . ')';
        echo $javascript;
    }
}

?>