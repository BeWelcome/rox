<?php


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
        $url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?'.$_SERVER['QUERY_STRING'];
        }
        
        // hack for HTTPS-Login
        if (strrpos($url, 'test') === false && strrpos($url, 'bw') === false  && strrpos($url, 'alpha') === false && strrpos($url, 'localhost') === false)
            $url = str_replace('http://','https://',$url);
        
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
        ?>
        <?php if ($small == true) { ?>
        <div class="login-widget-small" >
        <form id="main-login-form" method="post" action="<?=$url ?>">
          <?=$callback_tag ?>
          <?=$mem_recovery_tag ?>
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
                    <td>
                        <br /><input type="submit" value="Login" id="smallbutton" class="button"/>
                    </td>
                </tr>
            </table>
            
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
        <form method="post" action="<?=$url ?>">
          <?=$callback_tag ?>
          <?=$mem_recovery_tag ?>
          
          <table>
          <tr><td align="right">
            <label for="login-u"><?=$ww->Username ?></label>
          </td><td>
            <input type="text" id="login-u" name="u" />
          </td></tr>
          <tr><td align="right">
            <label for="login-p"><?=$ww->Password ?></label>
          </td><td>
            <input type="password" id="login-p" name="p" />
          </tr></td>
          <tr><td>&nbsp;</td><td>
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
