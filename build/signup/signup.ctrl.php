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
class SignupController extends RoxControllerBase {

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
		
				if ($_SESSION['Param']->FeatureSignupClose=="Yes") {
					// Todo : provide some nice view instead of this rough message
					die ("Sorry this feature is under maintenance. Volunteers are working on it, Please come back later") ; 
				}

        $request = $args->request;
        $model = new SignupModel();
        //ini_set("session.gc_maxlifetime", "20");
        
        if (isset($_SESSION['IdMember']) && !MOD_right::get()->hasRight('words')) {
            
            $this->redirect('bw/member.php?cid='.$_SESSION['Username']);
            
        } else switch (isset($request[1]) ? $request[1] : '') {
        
            // copied from TB:
            // checks e-mail address for validity and availability
            case 'checkemail':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if (!isset($_GET['email'])) {
                    echo '0';
                    PPHP::PExit();
                }
                if (!PFunctions::isEmailAddress($_GET['email'])) {
                    echo '0';
                    PPHP::PExit();
                }
                echo (bool)!$model->takeCareForNonUniqueEmailAddress($_GET['email']);
                PPHP::PExit();
                break;
                
            // copied from TB: rewiewed by JeanYves
            // checks Username for validity and availability
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
                echo (bool)!$model->handleInUse($request[2]);
                PPHP::PExit();
                break;
                
            case 'getRegions':
            // ignore current request, so we can use the last request
            PRequest::ignoreCurrentRequest();
            if (!isset($request[2])) {
                PPHP::PExit();
            }
            
            /**
            * probably not needed at this place?
            **/
            //$Geo = new GeoController;
            //echo "<br>model: ";
            //echo get_class($model);

            // $locations = $model->getRegions($country);
            // $out = '<select name="d_geoname" id="d_geoname" onchange="javascript: updateGeonames();">
                // <option value="">None</option>';
            // foreach ($locations as $code => $location) {
                // $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$location.'</option>';
            // }
            // $out .= '</select>';
            // return $out;
            // PPHP::PExit();
            // break;
                
            // case 'locationdropdowns':
            // // ignore current request, so we can use the last request
            // PRequest::ignoreCurrentRequest();
            // if (!isset($request[2])) {
                // PPHP::PExit();
            // }
            // $locations = $model->getAllLocations($country, $areacode);
            // $out = '<select name="d_geoname" id="d_geoname" onchange="javascript: updateGeonames();">
                // <option value="">None</option>';
            // foreach ($locations as $code => $location) {
                // $out .= '<option value="'.$code.'"'.($code == "$preselect" ? ' selected="selected"' : '').'>'.$location.'</option>';
            // }
            // $out .= '</select>';
            // return $out;
            // PPHP::PExit();
            // break;
            
            case 'terms':
				MOD_log::get()->write("Viewing terms","Signup") ;
                // the termsandconditions popup
                $page = new SignupTermsPopup();
                break;
                
            case 'privacy':
				MOD_log::get()->write("Viewing privacy","Signup") ;
                $page = new SignupPrivacyPopup();
                break;
            
            case 'confirm':  // or give it a different name?
                // this happens when you click the link in the confirmation email
                if (
                    !isset($request[2]) 
                    || !isset($request[3]) 
                    || !preg_match(User::HANDLE_PREGEXP, $request[2])
                    || !$model->handleInUse($request[2])
                    || !preg_match('/^[a-f0-9]{16}$/', $request[3])
                ) {
                    $error = true;
                } else {
                    $error = $model->confirmSignup($request[2], $request[3]);
                }
                $page = new SignupMailConfirmPage();
                $page->error = $error;
                // if (!isset($request[3])) {
                    // can't continue
                    // $page = new SignupMailConfirmPage_linkIsInvalid();
                // } else if (!$process = $model->getProcess($request)) {
                    // process id invalid
                    // $page = new SignupMailConfirmPage_linkIsInvalid();
                // } else {
                    // yeah, we can continue the process!
                    // $page = new SignupMailConfirmPage();
                    // $page->process = $process;
                // }
                break;
                
            case 'finish':
                $page = new SignupFinishPage();
                break;
                
            default:
                
                $page = new SignupPage();

                $page->step = (isset($request[1]) && $request[1]) ? $request[1] : '1';
				$StrLog="Entering Signup step: #".$page->step ;
				if (!empty($args->post["Username"])) {
					$StrLog=$StrLog." Username=[".$args->post["Username"]."]" ;
				}
				if (!empty($args->post["geonameid"])) {
					$StrLog=$StrLog." geonameid=[".$args->post["geonameid"]."]" ;
				}
				if (!empty($args->post["iso_date"])) {
					$StrLog=$StrLog." iso_date=[".$args->post["iso_date"]."]" ;
				}
				
				MOD_log::get()->write($StrLog,"Signup") ;
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
        
        //$mem_redirect->post = $vars;
        foreach ($args->post as $key => $value) {
            $_SESSION['SignupBWVars'][$key] = $value;
        }
        $vars = $_SESSION['SignupBWVars'];
        $request = $args->request;
        
        if (isset($request[1]) && $request[1] == '4') {
            $model = new SignupModel();
            
            $errors = $model->checkRegistrationForm($vars);
            
            if (count($errors) > 0) {
                // show form again
                $_SESSION['SignupBWVars']['errors'] = $errors;
                $mem_redirect->post = $vars;
                return false;
            }
            $model->polishFormValues($vars);
            
            if (!$idTB = $model->registerTBMember($vars)) {
                // MyTB registration didn't work
            } else {
                // signup on MyTB successful, yeah.
                $id = $model->registerBWMember($vars);
                $_SESSION['IdMember'] = $id;
                // $_SESSION['Username'] = $vars['username'];
                // $idTB = $id;
                
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
                unset($_SESSION['IdMember']);
                return 'signup/finish';
            }
        }
        return false;        
    }
}
?>
