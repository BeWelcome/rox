<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/
/**
 * user controller
 *
 * @package user
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
  * @version $Id: user.ctrl.php 217 2007-12-02 10:21:10Z lupochen $
 */
class UserController extends PAppController {
    /**
     * Model instance
     * 
     * @var User
     */
    private $_model;
    /**
     * View instance
     * 
     * @var View
     */
    private $_view;
    
    /**
     * Constructor
     * 
     * @param void
     */
    public function __construct() {
        parent::__construct();
        $this->_model = new User();
        $this->_view =  new UserView($this->_model);
        if (!PModules::moduleLoaded('user')) {
            throw new PException('Require module "user"!');
        }
    }
    
    /**
     * Destructor
     * 
     * @param void
     */
    public function __destruct() {
        unset($this->_model);
        unset($this->_view);
    }
    
    /**
     * Index function
     * 
     * Currently the index consists of following possible requests:
     * checkemail  - prints either "0" or "1" depending on e-mail validity
     * checkhandle - like "checkemail" with user handle
     * register    - registration form to page content 
     * 
     * @param void
     */
    public function index() {
        // index is called when http request = ./user
        $request = PRequest::get()->request;
        if (!isset($request[1]))
            $request[1] = '';
        switch($request[1]) {
            case 'avatar':
                PRequest::ignoreCurrentRequest();                
                if (!isset($request[2]) || !preg_match(User::HANDLE_PREGEXP, $request[2]) || !$userId = $this->_model->handleInUse($request[2]))
                    PPHP::PExit();
                $this->_view->avatar($userId);
                break;
                
            // checks e-mail address for validity and availability
            case 'checkemail':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if (!isset($_GET['e'])) {
                    echo '0';
                    PPHP::PExit();
                }
                if (!PFunctions::isEmailAddress($_GET['e'])) {
                    echo '0';
                    PPHP::PExit();
                }
                echo (bool)!$this->_model->emailInUse($_GET['e']);
                PPHP::PExit();
                break;
                
            // checks handle for validity and availability
            case 'checkhandle':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if (!isset($request[2])) {
                    echo '0';
                    PPHP::PExit();
                }
                if (!preg_match(User::HANDLE_PREGEXP, $request[2])) {
                    echo '0';
                    PPHP::PExit();
                }
                if (strpos($request[2], 'xn--') !== false) { // Don't allow IDN-Prefixes
                    echo '0';
                    PPHP::PExit();
                }
                echo (bool)!$this->_model->handleInUse($request[2]);
                PPHP::PExit();
                break;
                
            // confirms a registration
            case 'confirm':
                if (
                    !isset($request[2]) 
                    || !isset($request[3]) 
                    || !preg_match(User::HANDLE_PREGEXP, $request[2])
                    || !$this->_model->handleInUse($request[2])
                    || !preg_match('/^[a-f0-9]{16}$/', $request[3])
                ) {
                    $error = true;
                } else {
                    if ($this->_model->confirmRegister($request[2], $request[3])) {
                        $error = false;
                    } else {
                        $error = true;
                    }
                }
                ob_start();
                $this->_view->registerConfirm($error);
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;
                
            case 'find':
                $res = $this->_model->find($_GET['q']);
                ob_start();
                $this->_view->searchResult($res);
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;
                
            case 'friends':
                if (!$User = APP_User::login())
                    return false;
                $friends = $this->_model->getFriends($User->getId());
                ob_start();
                $this->_view->friends($friends);
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;
                
            case 'logout':
				$this->_model->logout();
				header("Location: " . PVars::getObj('env')->baseuri);
				break;
            
            // waiting approval message
                case 'waitingapproval':
				// now the teaser content
				ob_start();
				$this->_view->ShowInfoMessage('','');
                $str = ob_get_contents();
                $Page = PVars::getObj('page');
                $Page->teaserBar .= $str;
				ob_end_clean();
				// now the message content
                ob_start();
                $this->_view->ShowInfoMessage('WaitingForApprovalText','WaitingForApprovalTitle');
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;
                
            case 'settings':
                ob_start();
                $this->_view->settingsForm();
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;

            case 'password':
                ob_start();
				$this->_view->customStyles();
                $str = ob_get_contents();
                $Page = PVars::getObj('page');
                $Page->addStyles .= $str;
				ob_end_clean();
				// now the teaser content
				ob_start();
				$this->_view->teaser();
                $str = ob_get_contents();
                $Page = PVars::getObj('page');
                $Page->teaserBar .= $str;
				ob_end_clean();
				// now the content on the right
				ob_start();
				$this->_view->rightContent();
                $str = ob_get_contents();
                $Page = PVars::getObj('page');
                $Page->rContent .= $str;
				ob_end_clean();
				// main content
                ob_start();
				$this->_view->passwordForm();
                $str = ob_get_contents();
                $P = PVars::getObj('page');
                $P->content .= $str;
                ob_end_clean();
                break;

            default:
                if (preg_match(User::HANDLE_PREGEXP, $request[1])) {
                if (!isset($request[2]))
                    $request[2] = '';
                switch ($request[2]){
                    case 'pic':
                    if (!$User = APP_User::login())
                        return false;
                        
                    ob_start();
                    $picture = $this->_model->getPicture($request[1]);
                    $this->_view->picture($picture);
                    $str = ob_get_contents();
                    ob_end_clean();
                    $P = PVars::getObj('page');
                    $P->content .= $str;
                    break;
                    
                    default:  
                    // redirects to the old bw-based profile
                    header("Location: " . PVars::getObj('env')->baseuri . "bw/member.php?cid=" .$request[1]);           
                
                    // disabled TB-based userpage for now
                    /*    ob_start();
                                                $this->_view->userPage($request[1]);
                                                $str = ob_get_contents();
                                                ob_end_clean();
                                                $P = PVars::getObj('page');
                                                $P->content .= $str; */
                    break;                    
                }
                }
        }
    }
    
    /**
     * Displays login form at once
     * 
     * @param void
     */
    public function displayLoginForm($redirect_url = false) {
        $this->_view->loginForm($redirect_url);
    }
}
?>
