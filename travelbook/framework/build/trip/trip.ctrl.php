<?php
/**
 * trip controller
 *
 * @package trip
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: trip.ctrl.php 150 2006-07-26 12:06:23Z kang $
 */
class TripController extends PAppController {
    private $_model;
    private $_view;
    
    public function __construct() {
        parent::__construct();
        $this->_model = new Trip();
        $this->_view  = new TripView($this->_model);
    }
    
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    public function index() 
    {
        // index is called when http request = ./trip
        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = '';
        $User = APP_User::login();
        if ($User && $User->loggedIn()) {
            ob_start();
        	$this->_view->userbar();
            $str = ob_get_contents();
            ob_end_clean();
            $Page = PVars::getObj('page');
            $Page->content .= $str;
        }
        switch($request[1]) {
        	case 'create':
                if (!$User)
                    return false;
                ob_start();
                $this->_view->createForm();
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;
                break;
                
            default:
                break;
        }
    }
}
?>