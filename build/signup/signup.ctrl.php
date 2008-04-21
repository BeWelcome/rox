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
     * Index function
     *
     * Currently the index consists of following possible requests:
     * register    - registration form to page content
     * confirm   - confirmation redirect to signup
     *
     * @param void
     */
    public function index($args = false) {

        $request = $args->request;
        $model = new SignupModel();

        switch (isset($request[1]) ? $request[1] : '') {
            
            case 'terms':
                // the termsandconditions popup
                $page = new SignupTermsPopup();
                break;
                
            case 'privacy':
                $page = new SignupPrivacyPopup();
                break;
            
            case 'mailconfirm':  // or give it a different name?
                // this happens when you click the link in the confirmation email
                if (!isset($request[2])) {
                    // can't continue
                    $page = new SignupMailConfirmPage_linkIsInvalid();
                } else if (!$process = $model->getProcess($request[2])) {
                    // process id invalid
                    $page = new SignupMailConfirmPage_linkIsInvalid();
                } else {
                    // yeah, we can continue the process!
                    $page = new SignupMailConfirmPage();
                    $page->process = $process;
                }
                break;
                
            case 'finish':
                // what now?
                
            default:
                
                $page = new SignupPage();
                $page->model = $model;
                

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
        }
        
        return $page;
    }
    
    
    public function signupFormCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $vars = $args->post;
        
        
        $model = new SignupModel();
        
        
        $errors = $model->checkRegistrationForm($vars);
        
        if (count($errors) > 0) {
            // show form again
            $vars['errors'] = $errors;
            $mem_redirect->post = $vars;
            return false;
        }
        
        $model->polishFormValues($vars);
        
        if ($model->registerTBMember($vars)) {
            // MyTB registration didn't work
        } else {
            // signup on MyTB successful, yeah.
            $id = $model->registerBWMember($vars);
            $_SESSION['IdMember'] = $id;
            
            $vars['feedback'] .= 
                $model->takeCareForNonUniqueEmailAddress($vars['email']);
        
            $vars['feedback'] .=
                $model->takeCareForComputerUsedByBWMember();
            
            $model->writeFeedback($vars['feedback']);
                                    
            $View = new SignupView($model);
            // TODO: BW 2007-08-19: $_SYSHCVOL['EmailDomainName']
            define('DOMAIN_MESSAGE_ID', 'bewelcome.org');    // TODO: config
            $View->registerMail($idTB);
            $View->signupTeamMail($vars);
            
            return PVars::getObj('env')->baseuri.'signup/register/finish';
        }
    }
}
?>
