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
 * signup controller
 *
 * @package signup
 * @author Felix van Hove <fvanhove@gmx.de>
 */
class SignupController extends PAppController {

    /**
     * Model instance
     * 
     * @var Signup
     */
    private $_model;    // TODO: unused - remove?
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
        $this->_model = new Signup();
        $this->_view =  new SignupView($this->_model);
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
     * register    - registration form to page content
     * confirm   - confirmation redirect to signup	 
     * 
     * @param void
     */
    public function index() {

		// TODO: Remove after milestone 0.1-outreach
		// to be sure this signup is not called in the pending release of Rox
		header("Location: " . PVars::getObj('env')->baseuri . 'bw/signup.php');
		PPHP::PExit();
        
        $request = PRequest::get()->request;
        
        if (!isset($request[1]))
            $request[1] = '';
        
        switch($request[1]) {
            
            // stub for debugging
            case 'test':
                PPHP::PExit();
                break;
                
            case 'register':
                
                // start output buffering to save all to content
                ob_start();
                $this->_view->registerForm();
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;
                
			/*
                case 'confirm':
                ob_start();
                $username = "";
                $email = "";
                $this->_view->confirmation($username, $email);
                $str = ob_get_contents();
                ob_end_clean();
                $P = PVars::getObj('page');
                $P->content .= $str;
                break;
			*/

            case 'termsandconditions':
                $this->_view->showTermsAndConditions();
                PPHP::PExit();    // all layout done in template
        }
    }
}
?>
