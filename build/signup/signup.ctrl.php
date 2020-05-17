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
     *
     * @return SignupBasePage
     * @throws PException
     */
    public function index($args = false)
    {
        // In case Signup is closed
        if (isset($this->session->get('Param')->FeatureSignupClose) && $this->session->get('Param')->FeatureSignupClose=="Yes") {
            return new SignupClosedPage();
        }

        /*
         * Enable to check against DNS Blocklists

        if (MOD_dnsblock::get()->checkRemoteIp()) {
            return new SignupDNSBlockPage();
        }

        */

        $request = $args->request;
        $model = new SignupModel();

        if ($this->session->has( 'IdMember' ) && !MOD_right::get()->hasRight('words')) {
            if (!$this->session->has( 'Username' )) {
                $this->session->remove('IdMember');
                $page = new SignupProblemPage();
            } else {
                $this->redirect('members/'.$this->session->get('Username'));
            }
        } else switch (isset($request[1]) ? $request[1] : '') {

            // copied from TB:
            // checks e-mail address for validity and availability
            case 'checkemail':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if ((!isset($_REQUEST['field']) || (!isset($_REQUEST['value'])))) {
                    echo json_encode(
                        array(
                            "value" => '',
                            "valid" => false,
                            "message" => "Interesting an attack :-)"
                        ));
                    PPHP::PExit();
                }
                // check if email address is valid
                $count = 1; // Set count to one in case email address isn't valid
                $email = $model->checkEmail($_REQUEST['value']);
                if (false !== $email) {
                    $count = $model->emailInUse($email);
                }
                if ($count == 0) {
                    echo json_encode(
                        array(
                            "value" => $_REQUEST['value'],
                            "valid" => true,
                            "message" => "Email address is unique."
                        ));
                } else {
                    echo json_encode(
                        [
                            "value" => $_REQUEST['value'],
                            "valid" => false,
                            "message" => $model->getWords()->getSilent('SignupErrorEmailAddressAlreadyInUse')
                        ]
                    );
                }
                PPHP::PExit();
                break;

            // copied from TB: rewiewed by JeanYves
            // checks Username for validity and availability
            case 'checkhandle':
                // ignore current request, so we can use the last request
                PRequest::ignoreCurrentRequest();
                if ((!isset($_REQUEST['field']) || (!isset($_REQUEST['value'])))) {
                    echo json_encode(
                        array(
                            "value" => '',
                            "valid" => false,
                            "message" => "Interesting an attack :-)"
                        ));
                    PPHP::PExit();
                }
                $words = $model->getWords();
                $usernameValid = preg_match(SignupModel::HANDLE_PREGEXP, $_REQUEST['value']);
                if (!$usernameValid) {
                    echo json_encode(
                        array(
                            "value" => $_REQUEST["value"],
                            "valid" => $usernameValid,
                            "message" => $words->getSilent('SignupErrorWrongUsername')
                        ));
                    PPHP::PExit();
                }
                $valid = !$model->UsernameInUse($_REQUEST['value']);
                echo json_encode(
                    array(
                        "value" => $_REQUEST["value"],
                        "valid" => $valid,
                        "message" => $words->getFormatted('SignupErrorUsernameAlreadyTaken', $_REQUEST['value'])
                    ));
                PPHP::PExit();
                break;

            case 'finish':
                $page = new SignupFinishPage();
                break;

            default:
                $step = (isset($request[1]) && $request[1]) ? $request[1] : '1';
                $page = new SignupPage($step);
				$StrLog="Entering Signup step: #".$step ;
				MOD_log::get()->write($StrLog,"Signup") ;
                $page->setModel($model);
        }

        return $page;
    }


    public function signupFormCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $vars =$this->session->get('SignupBWVars');
        if (is_array($vars)) {
            $vars = array_merge($vars, $args->post);
        } else {
            $vars = $args->post;
        }
        $this->session->set('SignupBWVars', $vars);
        $vars = $this->session->get('SignupBWVars');
        $request = $args->request;

        if (!isset($request[1])) {
            $request[1] = 1;
        }

        if (isset($request[1])) {
            $step = intval($request[1]);
            $model = new SignupModel();
            if (($step >= 1) and ($step <= 4))
            {
                $errors = $model->checkRegistrationForm($vars, $step);
            }
            else
            {
                return false;
            }

            if (count($errors) > 0) {
                // show form again
                $vars['errors'] = $errors;
                $this->session->set( 'SignupBWVars', $vars );
                $mem_redirect->post = $vars;
                return false;
            }
            if ($step < '4') {
                $step++;
                $model->polishFormValues($vars);
                $this->session->set( 'SignupBWVars', $vars );
                $mem_redirect->post = $vars;
                return 'signup/' . ($step);
            }

            // Check all fields correctly set.
            $errors = $model->checkRegistrationForm($vars, 1);
            array_merge($errors, $model->checkRegistrationForm($vars, 2));
            array_merge($errors, $model->checkRegistrationForm($vars, 3));
            array_merge($errors, $model->checkRegistrationForm($vars, 4));

            if (count($errors) > 0) {
                $vars['errors'] = $errors;
                $this->session->set( 'SignupBWVars', $vars );
                $mem_redirect->post = $vars;
                return false;
            }

            // step 4 successfully done register new member
            $model->registerBWMember($vars);

            return 'signup/finish';
        }
        return false;
    }
}
