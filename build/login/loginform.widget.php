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

    public function render()
    {
        $layoutkit = $this->layoutkit;
        $words = $this->words;
        $ww = $this->ww;
        $formkit = $layoutkit->formkit;

        $callback_tag = $formkit->setPostCallback('LoginController', 'loginCallback');
        $mem_recovery_tag = $formkit->setMemForRecovery();

        $mem_redirect = $formkit->mem_from_redirect;
        $err = is_object($mem_redirect) ? $mem_redirect->errmsg : '';
        $baseuri = PVars::getObj('env')->baseuri;
        if (PVars::getObj('env')->force_ssl_sensitive) {
            $baseuri = PVars::getObj('env')->baseuri_https;
        }
        $url = $baseuri . htmlspecialchars(implode('/', PRequest::get()->request), ENT_QUOTES);
        $memoryExpiry = PVars::getObj('env')->rememberme_expiry;

        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?'.$_SERVER['QUERY_STRING'];
        }

        $logged_in = APP_User::IsBWLoggedIn("NeedMore,Pending");

        if ($logged_in) {
            // already logged in
            ?>
            <div>
              <p><?=$ww->LoginformAlreadyLogged($_SESSION['Username']) ?></p>
              <p><a href="user/logout"><?=$ww->Logout ?></a></p>
            </div>
            <?php
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
        <div class="info" id="login-widget">
        <?if ($err) : ?>
        <p class="note warning"><?=$err?></p>
        <? endif ?>
        <script type="text/javascript">
        	if(!navigator.cookieEnabled)
            	document.write("<p class=\"note warning\">Please enable cookies in your browser.</p>");
        </script>
        <form id="main-login-form" role="form" class="form-signin" method="post" action="<?=$url_login ?>">
        <h2><span class="hidden-xs"><?=$ww->Login ?></span></h2><h3><span id="BeWelcome" class="visible-xs center" >BeWelcome</span> <span id="opendoor" class="visible-xs center"> - <?php echo $words->get('IndexPageTeaserOpenDoor');?></span></h3>
          <?=$callback_tag ?>
          <?=$mem_recovery_tag ?>

            <?php if ($ItIsNotASubDomain) {  // Added because this is hidden for subdomain ?>
                            <label class="sr-only" for="login-u"><?=$ww->Username ?></label>
                            <input type="text" id="login-u" name="u" autofocus="" required="" placeholder="<?=$ww->Username ?>" class="form-control">
                            <label class="sr-only" for="login-p"><?=$ww->Password ?></label>
                            <input type="password" id="login-p" name="p" placeholder="<?=$ww->Password ?>" class="form-control">
                            <label for="login-r">
                            <input type="checkbox" id="login-r" value="<?=$ww->StayLoggedIn ?>" name="r">&nbsp;<?=$ww->StayLoggedIn ?>
                            </label>
            <?php }  // Added because this is hidden for subdomain ?>
                            <button type="submit" class="button btn-block"><?=$ww->IndexPageLoginSubmit; ?></button>
                            <p class="dividerbox center"><?=$ww->LoginformForgetPassword('<a href="resetpassword/">', '</a>') ?>
                                <span class="ordivider"></span>
                            </p>
                            <div class="center">
                                <h3><?=$ww->SignupNow ?></h3>
                                <?=$ww->IndexPageWord17('<a class="button btn-block" role="button" href="signup">', '</a>') ?>
                                <p class="visible-xs bw-row"><a class="btn btn-primary btn-block" role="button" href="tour"><?php echo $words->get('tour_take');?></a></p>

                            </div>
        </form>
        <script type="text/javascript">document.getElementById("login-u").focus();</script>
        </div>
        <?php
        if ($logged_in) {
            ob_end_clean();
            echo $words->flushBuffer();
        }
    }
}


?>
