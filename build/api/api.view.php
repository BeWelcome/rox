<?php
/**
 * API view class.
 *
 * @author Meinhard Benn <meinhard@bewelcome.org>
 */
class ApiView extends RoxAppView
{
    /**
     * Declaring private variable.
     */
    private $_model;

    /**
     * Constructor.
     */
    public function __construct(ApiModel $model) {
        $this->_model = $model;
    }

    /**
     * Send a raw plain text response.
     *
     * The PHP execution is aborted once response is sent to stop framework
     * from rendering a complete page.
     *
     * @param string $message Message text to display.
     */
    public function rawResponse($message) {
        header('Content-type: text/plain');
        echo $message . "\n";
        exit;
    }

    /**
     * Send a JSON response.
     *
     * The PHP execution is aborted once response is sent to stop framework
     * from rendering a complete page.
     *
     * @param object $content Object containing data fields.
     */
    public function jsonResponse($content) {
        header('Content-type: application/json');
        echo json_encode($content) . "\n";
        exit;
    }

    /**
     * Send a JSONP response.
     *
     * The PHP execution is aborted once response is sent to stop framework
     * from rendering a complete page.
     *
     * @param object $content Object containing data fields.
     * @param string $callback Name of JavaScript callback function.
     */
    public function jsonpResponse($content, $callback) {
        header('Content-type: application/javascript');
        $javascript = $callback . '(' . json_encode($content) . ')';
        echo $javascript . "\n";
        exit;
    }
}
