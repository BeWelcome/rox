<?php
/**
* Chat controller
*
* @package chat
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class ChatController extends PAppController {
	private $_model;
	private $_view;
	
	public function __construct() {
		parent::__construct();
		$this->_model = new Chat();
		$this->_view = new ChatView($this->_model);
		
		// if the applet is requested, pipe it through
		$request = PRequest::get()->request;
		if (isset($request[1]) && $request[1]) {
			if (isset($_SESSION['lastRequest'])) {
				PRequest::ignoreCurrentRequest();
			}
			$this->_view->passthroughApplet($request[1]);
		}
	}
	
	public function __destruct() {
		unset($this->_model);
		unset($this->_view);
	}

	/**
	* index is called when http request = ./chat
	*/
	public function index() {
		ob_start();
		
		$this->_view->displayChat();
		
		$Page = PVars::getObj('page');
		$Page->content .= ob_get_contents();
		ob_end_clean();
	}

}
?>
