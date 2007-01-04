<?php
/**
* forums controller
*
* @package forums
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class ForumsController extends PAppController {
	private $_model;
	private $_view;
	
	public function __construct() {
		parent::__construct();
		$this->_model = new Forums();
		$this->_view = new ForumsView($this->_model);
	}
	
	public function __destruct() {
		unset($this->_model);
		unset($this->_view);
	}
	
	/**
	* index is called when http request = ./forums
	*/
	public function index() {
		$request = PRequest::get()->request;
		$User = APP_User::login();

		$this->parseRequest();
		
		ob_start();
		if ($this->action == self::ACTION_VIEW) {
			if ($this->_model->isTopic()) {
				$this->_model->prepareTopic();
				$this->_view->showTopic();
			} else {
				$this->_model->prepareForum();
				$this->_view->showForum();
			}
		
		} else if ($this->action == self::ACTION_NEW) {
			if (!$User) {
				PRequest::home();
			}
			$callbackId = $this->createProcess();
			$this->_view->createTopic($callbackId);
			PPostHandler::clearVars($callbackId);
		} else if ($this->action == self::ACTION_REPLY) {
			if (!$User) {
				PRequest::home();
			}
			$callbackId = $this->replyProcess();
			$this->_view->replyTopic($callbackId);
			PPostHandler::clearVars($callbackId);
		} else {
			throw new PException('unexpected action!');
		}
		
		$Page = PVars::getObj('page');
		$Page->content .= ob_get_contents();
		ob_end_clean();
	}
	
	public function createProcess() {
		$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
		
		if (PPostHandler::isHandling()) {
			$this->parseRequest();
			return $this->_model->createProcess();
		} else {
			PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
			return $callbackId;
		}
	}
	
	public function replyProcess() {
		$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
		
		if (PPostHandler::isHandling()) {
			$this->parseRequest();
			return $this->_model->replyProcess();
		} else {
			PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
			return $callbackId;
		}
	}
	

	private $action = 0;
	const ACTION_VIEW = 0;
	const ACTION_NEW = 1;
	const ACTION_EDIT = 2;
	const ACTION_REPLY = 3;
	/**
	* Parses a request
	* Extracts the current action, geoname-id, country-code, admin-code, all tags and the threadid from the request uri
	*/
	private function parseRequest() {
		$request = PRequest::get()->request;
		foreach ($request as $r) {
			if ($r == 'new') {
				$this->action = self::ACTION_NEW;
			} else if ($r == 'edit') {
				$this->action = self::ACTION_EDIT;
			} else if ($r == 'reply') {
				$this->action = self::ACTION_REPLY;
			} else {
				$char = $r{0};
				$dashpos = strpos($r, '-');
				if ($dashpos === false) {
					$dashpos = strlen($r) - 1;
				} else {
					$dashpos--;
				}
				if ($char == 'g') { // Geoname-ID
					$this->_model->setGeonameid((int) substr($r, 1, $dashpos));
				} else if ($char == 'c') { // Countrycode
					$this->_model->setCountryCode(substr($r, 1, $dashpos));
				} else if ($char == 'a') { // Admincode
					$this->_model->setAdminCode(substr($r, 1, $dashpos));
				} else if ($char == 't') { // Tagid
					$this->_model->addTag((int) substr($r, 1, $dashpos));
				} else if ($char == 's') { // Subject-ID (Thread-ID)
					$this->_model->setThreadId((int) substr($r, 1, $dashpos));
				} else if ($char == 'k') { // Continent-ID
					$this->_model->setContinent(substr($r, 1, $dashpos));
				}
			}
		}
	}
}
?>
