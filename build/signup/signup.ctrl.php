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
    public function index($args = false)
    {
        // In case Signup is closed
        if (isset($_SESSION['Param']->FeatureSignupClose) && $_SESSION['Param']->FeatureSignupClose=="Yes") {
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

        if (isset($_SESSION['IdMember']) && !MOD_right::get()->hasRight('words')) {
            if (!isset($_SESSION['Username'])) {
                unset($_SESSION['IdMember']);
                $page = new SignupProblemPage();
            } else {
                $this->redirect('members/'.$_SESSION['Username']);
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
                $users = $model->takeCareForNonUniqueEmailAddress($_REQUEST['value']);
                if ($users == '') {
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
                $usernameValid = preg_match(User::HANDLE_PREGEXP, $_REQUEST['value']);
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

            case 'confirm':  // or give it a different name?
                // this happens when you click the link in the confirmation email
                if (
                    !isset($request[2])
                    || !isset($request[3])
                    || !preg_match(User::HANDLE_PREGEXP, $request[2])
                    || !$model->UsernameInUse($request[2])
                    || !preg_match('/^[a-f0-9]{16}$/', $request[3])
                ) {
                    $error = 'InvalidLink';
                } else {
                    $error = $model->confirmSignup($request[2], $request[3]);
                }
                $page = new SignupMailConfirmPage();
                $page->error = $error;
                break;

            case 'resendmail':  // shown when clicking on the link in the MailToConfirm error message
                $error = '';
                if (!isset($request[2])) {
                    $error = 'InvalidLink';
                } else {
                    $resent = $model->resendConfirmationMail($request[2]);
                    if ($resent !== true) {
                        $error = $resent;
                    }
                }
                $page = new SignupResentMailPage();
                $page->error = $error;
                break;

            case 'finish':
                $page = new SignupFinishPage();
                break;

            default:
                $step = (isset($request[1]) && $request[1]) ? $request[1] : '1';
                $page = new SignupPage($step);
				$StrLog="Entering Signup step: #".$page->step ;
				MOD_log::get()->write($StrLog,"Signup") ;
                $page->model = $model;
        }

        return $page;
    }


    public function signupFormCallback($args, $action, $mem_redirect, $mem_resend)
    {

        //$mem_redirect->post = $vars;
        foreach ($args->post as $key => $value) {
            $_SESSION['SignupBWVars'][$key] = $value;
        }

		$StrLog="Entering signupFormCallback " ;
		if (!empty($args->post["Username"])) {
			$StrLog=$StrLog." Username=[".$args->post["Username"]."]" ;
		}
        if (!empty($args->post["location-geoname-id"])) {
            $StrLog=$StrLog." geonameid=[".$args->post["location-geoname-id"]."]" ;
        }
        if (!empty($args->post["location-latitude"])) {
            $StrLog=$StrLog." latitude=[".$args->post["location-latitude"]."]" ;
        }
        if (!empty($args->post["location-longitude"])) {
            $StrLog=$StrLog." longitude=[".$args->post["location-longitude"]."]" ;
        }
		if (!empty($args->post["iso_date"])) {
			$StrLog=$StrLog." iso_date=[".$args->post["iso_date"]."]" ;
		}

		MOD_log::get()->write($StrLog,"Signup") ;

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

                $vars['feedback'] .=
                    $model->takeCareForNonUniqueEmailAddress($vars['email']);

                $vars['feedback'] .=
                    $model->takeCareForComputerUsedByBWMember();

                $model->writeFeedback($vars['feedback']);

                $View = new SignupView($model);
                // TODO: BW 2007-08-19: $_SYSHCVOL['EmailDomainName']
                // look at that ... a two years plus old todo :) ... and now four years plus :P
                // finally 7 years and counting...

                define('DOMAIN_MESSAGE_ID', 'bewelcome.org');    // TODO: config
                $View->registerMail($vars, $id, $idTB);
                unset($_SESSION['IdMember']);
                return 'signup/finish';
            }
        }
        return false;
    }
}
