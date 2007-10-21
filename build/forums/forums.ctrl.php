<?php
/**
* forums controller
*
* @package forums
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id: forums.ctrl.php 32 2007-04-03 10:22:22Z marco_p $
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

  public function topMenu($currentTab) {
        $this->_view->topMenu($currentTab);
  }
  
	/**
	* index is called when http request = ./forums
	*/
	public function index() {
        if (PPostHandler::isHandling()) {
            return;
        }
		$request = PRequest::get()->request;
		$User = APP_User::login();

		// first include the col2-stylesheet
        ob_start();
		echo $this->_view->customStyles();
        $str = ob_get_contents();
        $Page = PVars::getObj('page');
        $Page->addStyles .= $str;
        $Page->currentTab = 'forums';
		ob_end_clean();	
		
		$this->parseRequest();
		ob_start();
		$this->_model->prepareForum();
		$this->_view->teaser();
        $str = ob_get_contents();
        ob_end_clean();
        $Page = PVars::getObj('page');
        $Page->teaserBar .= $str;

		ob_start();
		if ($this->action == self::ACTION_VIEW) {
			if ($this->_model->isTopic()) {
				$this->_model->prepareTopic();
				$this->_view->showTopic();
			} else {
				$this->_model->prepareForum();
				if ($this->isTopLevel) {
					$this->_view->showTopLevel();
				} else {
					$this->_view->showForum();
				}
			}
		} else if ($this->action == self::ACTION_RULES) {
		    $this->_view->rules();
		} else if ($this->action == self::ACTION_NEW) {
			if (!$User) {
				PRequest::home();
			}
			$this->_model->prepareForum();
			$callbackId = $this->createProcess();
			$this->_view->createTopic($callbackId);
			PPostHandler::clearVars($callbackId);
		} else if ($this->action == self::ACTION_REPLY) {
			if (!$User) {
				PRequest::home();
			}
			$this->_model->prepareForum();
			$this->_model->prepareTopic();
			$this->_model->initLastPosts();
			$callbackId = $this->replyProcess();
			$this->_view->replyTopic($callbackId);
			PPostHandler::clearVars($callbackId);
		} else if ($this->action == self::ACTION_SUGGEST) {
			// ignore current request, so we can use the last request
			PRequest::ignoreCurrentRequest();
			if (!isset($request[2])) {
				PPHP::PExit();
			}
			$new_tags = $this->_model->suggestTags($request[2]);
			echo $this->_view->generateClickableTagSuggestions($new_tags);
			PPHP::PExit();
			break;		
		} else if ($this->action == self::ACTION_LOCATIONDROPDOWNS) {
			// ignore current request, so we can use the last request
			PRequest::ignoreCurrentRequest();
			if (!isset($request[2])) {
				PPHP::PExit();
			}
			echo $this->_view->getLocationDropdowns();
			PPHP::PExit();
			break;		
		} else if ($this->action == self::ACTION_DELETE) {
			if (!$User || !$User->hasRight('delete@forums')) {
				PRequest::home();
			}
			$this->delProcess();
		} else if ($this->action == self::ACTION_EDIT) {
			if (!$User) {
				PRequest::home();
			}
			$callbackId = $this->editProcess();
			$this->_model->prepareForum();
			$this->_model->getEditData($callbackId);
			$this->_view->editPost($callbackId);
			PPostHandler::clearVars($callbackId);
		} else if ($this->action == self::ACTION_SEARCH_USERPOSTS) {
			if (!isset($request[2])) {
				PPHP::PExit();
			}
			$this->searchUserposts($request[2]);
		} else {
			if (PVars::get()->debug) {
				throw new PException('unexpected action!');
			} else {
				PRequest::home();
			}
		}
		
		$Page = PVars::getObj('page');
		$Page->content .= ob_get_contents();
		ob_end_clean();
	}
	
	private function searchUserposts($user) {
		$posts = $this->_model->searchUserposts($user);
		$this->_view->displaySearchResultPosts($posts);
	}
	
	public function editProcess() {
		$callbackId = PFunctions::hex2base64(sha1(__METHOD__));
		
		if (PPostHandler::isHandling()) {
			$this->parseRequest();
			return $this->_model->editProcess();
		} else {
			PPostHandler::setCallback($callbackId, __CLASS__, __METHOD__);
			return $callbackId;
		}
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
	
	public function delProcess() {
		$this->parseRequest();
		$this->_model->delProcess();
	}

	private $action = 0;
	private $isTopLevel = true;
	const ACTION_VIEW = 0;
	const ACTION_NEW = 1;
	const ACTION_EDIT = 2;
	const ACTION_REPLY = 3;
	const ACTION_SUGGEST = 4;
	const ACTION_DELETE = 5;
	const ACTION_LOCATIONDROPDOWNS = 6;
	const ACTION_SEARCH_USERPOSTS = 7;
	const ACTION_RULES = 8;
	
	/**
	* Parses a request
	* Extracts the current action, geoname-id, country-code, admin-code, all tags and the threadid from the request uri
	*/
	private function parseRequest() {
		$request = PRequest::get()->request;
		if (isset($request[1]) && $request[1] == 'suggestTags') {
			$this->action = self::ACTION_SUGGEST;
		} else if (isset($request[1]) && $request[1] == 'user') {
			$this->action = self::ACTION_SEARCH_USERPOSTS;
		} else if (isset($request[1]) && $request[1] == 'rules') {
		    $this->action = self::ACTION_RULES;
		} else {
			foreach ($request as $r) {
				if ($r == 'new') {
					$this->action = self::ACTION_NEW;
				} else if ($r == 'edit') {
					$this->action = self::ACTION_EDIT;
				} else if ($r == 'reply') {
					$this->action = self::ACTION_REPLY;
				} else if ($r == 'delete') {
					$this->action = self::ACTION_DELETE;
				} else if (eregi('page([0-9]+)', $r, $regs)) {
					$this->_model->setPage($regs[1]);
				} else if ($r ==  'locationDropdowns') {
					$this->action = self::ACTION_LOCATIONDROPDOWNS;
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
						$this->isTopLevel = false;
					} else if ($char == 'c') { // Countrycode
						$this->_model->setCountryCode(substr($r, 1, $dashpos));
						$this->isTopLevel = false;
					} else if ($char == 'a') { // Admincode
						$this->_model->setAdminCode(substr($r, 1, $dashpos));
						$this->isTopLevel = false;
					} else if ($char == 't') { // Tagid
						$this->_model->addTag((int) substr($r, 1, $dashpos));
						$this->isTopLevel = false;
					} else if ($char == 's') { // Subject-ID (Thread-ID)
						$this->_model->setThreadId((int) substr($r, 1, $dashpos));
						$this->isTopLevel = false;
					} else if ($char == 'k') { // Continent-ID
						$this->_model->setContinent(substr($r, 1, $dashpos));
						$this->isTopLevel = false;
					} else if ($char == 'm') { // Message-ID (Single Post)
						$this->_model->setMessageId(substr($r, 1, $dashpos));
						$this->isTopLevel = false;
					}
				}
			}
		}
	}
}
?>
