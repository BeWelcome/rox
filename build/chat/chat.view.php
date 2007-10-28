<?php
/**
* Chat view
*
* @package chat
* @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
* @copyright Copyright (c) 2005-2006, myTravelbook Team
* @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
* @version $Id$
*/

class ChatView extends PAppView {
	private $_model;
	
	public function __construct(Chat &$model) {
		$this->_model =& $model;
	}

	public function displayChat() {
		$User = APP_User::login();
		if ($User) {
			$nick = $User->getHandle();
		} else {
			$nick = 'Guest???';
		}
		
		$config = PVars::getObj('config_chat');
		if ($config && isset($config->host) && $config->host) {
			$chat_host = $config->host;
		} else {
			$chat_host = $_SERVER['HTTP_HOST'];
		}
		if ($config && isset($config->port) && $config->port) {
			$chat_port = $config->port;
		} else {
			$chat_port = 6667;
		}
		if ($config && isset($config->channel) && $config->channel) {
			$chat_channel = $config->channel;
		} else {
			$chat_channel = '#mytravelbook';
		}
		
		require TEMPLATE_DIR.'apps/chat/chat.php';
	}

	public function passthroughApplet($req) {
		$loc = PApps::getBuildDir().'chat/pjirc/'.$req;
		if (!file_exists($loc)) {
			exit;
		}
		$headers = apache_request_headers();
		// Checking if the client is validating his cache and if it is current.
		if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($loc))) {
			// Client's cache IS current, so we just respond '304 Not Modified'.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($loc)).' GMT', true, 304);
		} else {
			// File not cached or cache outdated, we respond '200 OK' and output the image.
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($loc)).' GMT', true, 200);
			header('Content-Length: '.filesize($loc));
		}
		header('Content-type: text/css');
		@copy($loc, 'php://output');
		exit;
	}
}
?>