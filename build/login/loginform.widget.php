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
     * widget for login form
     *
     * @package Apps
     * @subpackage Widget
     */


class LoginFormWidget extends RoxWidget
{
    private $_memory = false;

    public function render($small = false)
    {
        $layoutkit = $this->layoutkit;
        $words = $this->words;
        $ww = $this->ww;
        $formkit = $layoutkit->formkit;

        $callback_tag = $formkit->setPostCallback('LoginController', 'loginCallback');
        $mem_recovery_tag = $formkit->setMemForRecovery();

        $mem_redirect = $formkit->mem_from_redirect;
        $err = is_object($mem_redirect) ? $mem_redirect->errmsg : '';
        $url = PVars::getObj('env')->baseuri . htmlspecialchars(implode('/', PRequest::get()->request), ENT_QUOTES);
        $memoryExpiry = PVars::getObj('env')->rememberme_expiry;

        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?'.$_SERVER['QUERY_STRING'];
        }

        // hack for HTTPS-Login
        if (strrpos($url, 'alpha.bewelcome.org') === false &&
                strrpos($url, 'localhost') === false &&
                PVars::getObj('development')->avoid_https != 1 ) {
            $url = str_replace('http://','https://',$url);
        }

        $logged_in = APP_User::IsBWLoggedIn("NeedMore,Pending");

        if ($logged_in) {
            // already logged in
            // why show this form?
            if ($small != true) {
            ?>
            <div>
              <p><?=$ww->LoginformAlreadyLogged($_SESSION['Username']) ?></p>
              <p><a href="user/logout"><?=$ww->Logout ?></a></p>
            </div>
            <?php
            } else {
            ?>
                <span id="logout"><a href="user/logout" ><?=$words->get('Logout')?></a></span>
            <?php
            }
            // for translators, we want links for all the translations,
            // because otherwise they will never see the login form words.
            // we make this happen with a little trick :)
            ob_start();
            $ww = $this->wwsilent;
        }


        // Adds for subdomain to forward login (not definitive, under discussions), jy 5/7/2009
        $ItIsNotASubDomain=true ;
        $url_login=$url ;


        // Test if it is a knowm subdomain, and if so change prepare forwarding to a login widget on www
        if  (  (strrpos($url_login, 'fr.') !== false)
            or (strrpos($url_login, 'de.') !== false)
//          or (strrpos($url_login, 'localhost') !== false)
        ) {
            $ItIsNotASubDomain=false ;
            $url_login="http://www.bewelcome.org/login#login-widget" ;
        } // end of adds for subdomains


        ?>
        <?php if ($small == true) { ?>
        <div class="login-widget-small" >
        <form id="main-login-form" method="post" action="<?=$url_login ?>">
          <?=$callback_tag ?>
          <?=$mem_recovery_tag ?>
            <?php if ($ItIsNotASubDomain) { // Added because this is hidden for subdomain ?>
                <table>
                    <tr>
                        <td>
                            <label for="login-u"><?=$ww->Username ?></label><br />
                            <input type="text" id="login-u" name="u" />
                        </td>
                        <td>
                            <label for="login-p"><?=$ww->Password ?></label><br />
                            <input type="password" id="login-p" name="p" />
                        </td>
						<td title="<?=$ww->StayLoggedIn_SecurityHint($memoryExpiry) ?>">
							<input type="checkbox" id="login-r" name="r" /><label for="login-r"><?=$ww->StayLoggedIn ?></label>
						</td>
                        <td>
                            <br /><input type="submit" value="Login" id="smallbutton" class="button"/>
                        </td>
                    </tr>
                </table>
        <?php }  // Added because this is hidden for subdomain ?>
        </form>

        <script type="text/javascript">
            document.getElementById("login-u").focus();
        </script>

        </div>
        <?php } else { ?>
        <div class="info" id="login-widget">
        <h3><?=$ww->Login ?></h3>
        <?if ($err) : ?>
        <p class="note warning"><?=$err?></p>
        <? endif ?>
        <script type="text/javascript">
        	if(!navigator.cookieEnabled)
            	document.write("<p class=\"note warning\">Please enable cookies in your browser.</p>");
        </script>
        <form method="post" action="<?=$url_login ?>">
          <?=$callback_tag ?>
          <?=$mem_recovery_tag ?>

            <?php if ($ItIsNotASubDomain) {  // Added because this is hidden for subdomain ?>
                <table>
                    <tr>
                        <td align="right">
                            <label for="login-u"><?=$ww->Username ?></label>
                        </td>
                        <td>
                            <input type="text" id="login-u" name="u" />
                        </td>
                    </tr>
                    <tr>
                        <td align="right">
                            <label for="login-p"><?=$ww->Password ?></label>
                        </td>
                        <td>
                            <input type="password" id="login-p" name="p" />
                        </td>
                    </tr>
					<tr>
						<td></td>
						<td title="<?=$ww->StayLoggedIn_SecurityHint($memoryExpiry) ?>">
							<input type="checkbox" id="login-r" name="r" />&nbsp;<label for="login-r"><?=$ww->StayLoggedIn ?></label>
						</td>
					</tr>
                <tr><td>&nbsp;</td><td>
            <?php }  // Added because this is hidden for subdomain ?>
            <input type="submit" value="Login" class="button"/>
          </td></tr></table>
          <p><?=$ww->LoginformForgetPassword('<a href="bw/lostpassword.php">', '</a>') ?><br /><br /></p>
          <h3><?=$ww->SignupNow ?></h3>
          <p><?=$ww->IndexPageWord17('<a class="button" href="signup">', '</a>') ?></p>
        </form>
        <script type="text/javascript">document.getElementById("login-u").focus();</script>
        </div>
        <?php } ?>
        <?php
        if ($logged_in) {
            ob_end_clean();
            echo $words->flushBuffer();
        }
    }
}


?>
