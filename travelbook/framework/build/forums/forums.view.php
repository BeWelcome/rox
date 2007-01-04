<?php
/**
* Forums view
*
* @package forums
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class ForumsView extends PAppView {
	private $_model;
	
	public function __construct(Forums &$model) {
		$this->_model =& $model;
	}
	
	/**
	* Create a new topic in the current forum
	*/
	public function createTopic(&$callbackId) {
		$allow_title = true;
		require TEMPLATE_DIR.'apps/forums/editcreateform.php';
		
	}
	
	public function replyTopic(&$callbackId) {
		$allow_title = false;
		require TEMPLATE_DIR.'apps/forums/editcreateform.php';
		
	}
	
	/**
	* Display a topic
	*/
	public function showTopic() {
		$topic = $this->_model->getTopic();
		$request = PRequest::get()->request;
		$uri = implode('/', $request);
		$uri = rtrim($uri, '/').'/';
		
		require TEMPLATE_DIR.'apps/forums/topic.php';
	}

	
	/**
	* Display a forum
	*/
	public function showForum() {
		$boards = $this->_model->getBoard();
		$request = PRequest::get()->request;
		$uri = implode('/', $request);
		$uri = rtrim($uri, '/').'/';
		
		
		require TEMPLATE_DIR.'apps/forums/board.php';
		
	}
}
?>