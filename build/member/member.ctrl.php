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
 * member controller
 *
 * @package member
 * @author Michael Dettbarn (bw: lupochen)
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
  * @version $Id: member.ctrl.php 217 2007-03-03 10:21:10Z lupochen $
 */
class MemberController extends PAppController {
    /**
     * Model instance
     * 
     * @var Member
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
        $this->_model = new Member();
        $this->_view =  new MemberView($this->_model);
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
                    if (!isset($request[3]))
                        $request[3] = '';
                    switch ($request[3]){
                        
                        case 'comments':
                            if (!$User = APP_User::login()) {
                            $friends = $this->_model->getComments($User->getId());
                            } else {
                            }
                            ob_start();
                            $this->_view->friends($friends);
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->content .= $str;
                            break;
                            
                        default:
                        // redirects to the old bw-based profile
                        //if (preg_match(User::HANDLE_PREGEXP, $request[1])) {
                        //header("Location: " . PVars::getObj('env')->baseuri . "bw/member.php?cid=" .$request[1]);           
                        //} else {
                        //}
                        // disabled TB-based userpage for now -- TESTING
                            
                            $IdMember = APP_User::memberId($request[1]);
                            $m2 = $this->_model->prepareProfileHeader($request[1]);
                            $m = $this->_model->prepareProfileContent($request[1],$m2);
                            
                            ob_start();
                            $TGroups = $this->_model->getmembersgroups($IdMember);
                            $this->_view->profile($m,$TGroups);
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->content .= $str; 
                            
                            ob_start();
                            $this->_view->profileteaser($m);
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->teaserBar .= $str; 
                            
                            ob_start();
                            $Relations = $this->_model->getmembersrelations($IdMember);
                            $this->_view->profilemenu($Relations,$m);
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->newBar .= $str; 
                            
                            ob_start();
                            $Relations = $this->_model->getmembersrelations($IdMember);
                            $this->_view->relations($Relations);
                            $str = ob_get_contents();
                            ob_end_clean();
                            $P = PVars::getObj('page');
                            $P->newBar .= $str; 
                            
                        break;        
                    }
            
                }
        }
    }
    
}
?>
