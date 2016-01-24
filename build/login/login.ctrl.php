<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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
     * @author Lupochen
     */
    /**
     * login app controller
     *
     * @package Apps
     * @subpackage Login
     */


class LoginController extends RoxControllerBase
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new LoginModel;
    }

    public function loginCallback($args, $action, $mem_for_redirect)
    {
        $count = $action->count;
        $redirect_req = $action->redirect_req;

        $post = $args->post;
        $request = $args->request;


        // note:
        // all the echos are buffered by the framework,
        // and sent out after the redirect.

        $errmsg = '';
        if (empty($post['u'])) {
            $errmsg = $this->getWords()->get("LoginErrorNoUsername");

        } else if (!$bw_member = $this->model->getBWMemberByUsername($username = trim($post['u']))) {
            $errmsg = $this->getWords()->get("LoginErrorWrongCredentials");

        } else if (!is_string($post['p']) || strlen($post['p'])==0) {
            if (PVars::getObj('development')->skip_password_check != 1) {
                $errmsg = $this->getWords()->get("LoginErrorNoPassword");
            }
        } else if (!$this->model->checkBWPassword($bw_member, $post['p'])) {
            if (PVars::getObj('development')->skip_password_check != 1) {
                $errmsg = $this->getWords()->get("LoginErrorWrongCredentials");
            }
        }

        if ($errmsg != '') {
			$mem_for_redirect->errmsg = $errmsg;
			// error message on top disabled. We're using a div inside the login-form instead!
            //echo '<div id="loginmessage" class="false">' . $errmsg . '</div>';
        } else {
            // bw member exists, and pw matches.

            // what about the tb user?
            if (!$tb_user = $this->model->getTBUserForBWMember($bw_member)) {
                // no, he's not in TB. Buuuh.
                // Create new?
                echo "<div>no tb user found with handle = '$bw_member->Username'. Trying to repair that.</div>";
                if (!$success = $this->model->createMissingTBUser($bw_member, $password)) {
                    echo "<div>Didn't work.</div>";
                } else if ('same_id' == $success) {
                    echo "
                    <div>Created new tb user with same id</div>
                    <div>(Username: '$bw_member->Username', BW-id: $bw_member->id, TB-id: $tb_user->id)</div>";
                } else if ('different_id' == $success) {
                    echo "
                    <div>Created new tb user with different id</div>
                    <div>(Username: '$bw_member->Username', BW-id: $bw_member->id, TB-id: $tb_user->id)</div>";
                }
            }

            // In case we want to show a message
            $words = $this->getWords();

            if (!$tb_user = $this->model->getTBUserForBWMember($bw_member)) {
                echo "<div id='loginmessage' class='false'>still no tb user found with handle = '$bw_member->Username'. Giving up.</div>";
            } else {
                if (!$this->model->setBWMemberAsLoggedIn($bw_member)) {
                    // something in the status was not ok.
                    switch ($bw_member->Status) {
                        case 'SuspendedBeta':
                            echo '<div id="loginmessage" class="false">'. $this->getWords()->get("LoginErrorSuspended") .'</div>';
                            break;
                        case 'PassedAway':
                            echo '<div id="loginmessage" class="false">'. $this->getWords()->get("LoginErrorPassedAway") .'</div>';
                            break;
                    	case 'MailToConfirm':
                    	    $url = PVars::getObj('env')->baseuri . 'signup/resendmail/' . htmlspecialchars($bw_member->Username);
                    	    echo '<div id="loginmessage" class="false">'. $words->get("LoginErrorMailToConfirm",
                    	       $words->getSilent('LoginErrorMailToConfirmHere'), '<a href="' . $url . '">', '</a>') .'</div>';
                    	    break;
                    	default:
                    	    echo '<div id="loginmessage" class="false">'. $this->getWords()->get("LoginErrorWrongStatus", $bw_member->Status) .'</div>';
                    }
                } else {
                    if ($bw_member->Status != 'Active' && $bw_member->Status != 'ChoiceInactive')
                    {
                    echo '<div id="loginmessage_wrapper">';
                    echo '<div id="loginmessage">login successful</div>';
                    echo '</div>';

                    ?>
                    <script type="text/javascript">
                        document.observe("dom:loaded", function() {
                          // initially hide the login message after a few seconds
                          Effect.SlideUp('loginmessage_wrapper',{delay: 1.5});
                        });
                    </script>

                    <?
                    }
                    $this->model->setupBWSession($bw_member);
                    $this->model->setTBUserAsLoggedIn($tb_user);
                    if ($bw_member->LastLogin == '0000-00-00 00:00:00') {
                        $firstLogin = true;
                    } else {
                        $firstLogin = false;
                    }
                    $flashNotice = '';
                    if ($firstLogin) {
                        $flashNotice .= '<p>' . $words->get('LoginFirstLogin', $bw_member->Username) . '</p>';
                    }
                    if ($this->model->setPreferredLanguage( $bw_member)) {
                        $flashNotice .= '<p>' . $words->get('LoginPreferredLanguageSet', $words->getSilent('lang_' . $_SESSION['lang'])) . '</p>';
                    }
                    if (!empty($flashNotice)) {
                        $this->setFlashNotice($flashNotice);
                    }
                    if (!empty($post['r']) && $post['r']) { // member wants to stay logged in
                        $bw_member->refreshMemoryCookie(true);
                    }
                    if ($firstLogin) {
                        $redirect_url = 'editmyprofile';
                        return $redirect_url;
                    }
                    if (isset($request[0]) && 'login' == $request[0]) {
                        $redirect_url = implode('/', array_slice($request, 1));
                        if (!empty($_SERVER['QUERY_STRING'])) {
                            $redirect_url .= '?'.$_SERVER['QUERY_STRING'];
                        }
                        return $redirect_url;
                    }
                }
            }
        }
        if (empty($errmsg)) {
            return true;
        }
        return false;
    }

    /**
     * displays a login page with a login widget
     *
     * @access public
     * @return object
     */
    public function logIn()
    {
        $redirect_url = implode('/', array_slice($this->args_vars->request, 1));
        if (!empty($_SERVER['QUERY_STRING'])) {
            $redirect_url .= '?'.$_SERVER['QUERY_STRING'];
        }
        if ($this->getLoggedInMember())
        {
            $this->redirect($redirect_url);
        }
        return new LoginPage;
    }

    /**
     * logs a member out of the site
     *
     * @access public
     */
    public function logOut()
    {
        $this->model->logout();
        $this->redirectAbsolute($this->router->url('start'));
    }

    public function close() {
        $messageId = $this->route_vars['id'];
        $loginMessage = new LoginMessage($messageId);
        $loginMessage->acknowledgeMessage($this->model->getLoggedInMember());
        $redirect = $_SERVER['HTTP_REFERER'];
        if (empty($redirect)) {
            $redirect = PVars::getObj('env')->baseuri . '/';
        } else {
            // don't redirect infinitely
            if (strpos($redirect, '/close/') !== false) {
                $redirect = PVars::getObj('env')->baseuri . '/';
            }
        }
        $this->redirectAbsolute($redirect);
    }
}
