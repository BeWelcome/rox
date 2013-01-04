<?php
/**
 * API controller class.
 *
 * @author Meinhard Benn <meinhard@bewelcome.org>
 */
class ApiController extends RoxControllerBase
{
    /**
     * Declaring private variables.
     */
    private $_model;
    private $_view;

    /**
     * Defining class constants.
     */
    const FORMAT_JSON = 'json';
    const FORMAT_JS = 'js';

    /**
     * Defining formats supported by the API.
     */
    public $supportedFormats = array(self::FORMAT_JSON, self::FORMAT_JS);

    /**
     * Constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new ApiModel();
        $this->_view  = new ApiView($this->_model);
    }

    /**
     * Deconstructor.
     */
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }

    /**
     * Check if requested data format is valid.
     *
     * This will do nothing if format is supported and has neccesary
     * parameters, but will send a raw error message response otherwise.
     */
    public function checkFormat() {
        $format = $this->_getFormat();
        if (in_array($format, $this->supportedFormats) == false) {
            $this->_view->rawResponse('Invalid request: Format "' . $format
                . '" not supported');
        }
        $callback = $this->_getCallback();
        if ($format == self::FORMAT_JS && $callback == false) {
            $this->_view->rawResponse(
                'Invalid request: JSONP callback missing');
        }
    }

    /**
     * Send a successful response.
     *
     * @param object $data Object containing data fields for response.
     */
    public function success($data) {
        $this->response('success', $data);
    }

    /**
     * Send a response containing an error message, using the requested format.
     *
     * @param string $message Error message.
     */
    public function error($message) {
        $data = new stdClass;
        $data->errorMessage = $message;
        $this->response('error', $data);
    }

    /**
     * Prepare response data and send it to view, using requested format.
     *
     * @param string $resultType Descriptive result type included in response.
     * @param object $data Object containing data fields for response.
     */
    public function response($resultType, $data) {
        /* "result" should be the first field in the response, so we are
           inserting it before the data. The easiest way to do this is
           temporarily converting $data into an array: */
        $result = array('result' => $resultType);
        $content = (object) array_merge($result, (array) $data);

        $format = $this->_getFormat();
        if($format == self::FORMAT_JSON) {
            $this->_view->jsonResponse($content);
        } else if ($format == self::FORMAT_JS) {
            $callback = $this->_getCallback();
            $this->_view->jsonpResponse($content, $callback);
        }
    }

    /**
     * Default index.
     *
     * Sends placeholder message as plain text response.
     */
    public function index() {
        $this->_view->rawResponse('Does not compute');
    }

    /**
     * Member API action.
     *
     * Fetches data, checks permissions and initiates response.
     */
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

    /**
     * Fetch callback URL parameter.
     *
     * @return string|bool Content of parameter or false if not set.
     */
    private function _getCallback() {
        if (isset($this->args_vars->get['callback']) &&
            $this->args_vars->get['callback'] != '') {
            return $this->args_vars->get['callback'];
        } else {
            return false;
        }
    }

    /**
     * Get request format.
     *
     * @return string Request format, e.g. "json".
     */
    private function _getFormat() {
        return $this->route_vars['format'];
    }
}
