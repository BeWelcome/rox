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
        
        $url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?'.$_SERVER['QUERY_STRING'];
        }
        
        // hack for HTTPS-Login
        if (strrpos($url, 'test') === false && strrpos($url, 'bw') === false && 
	    strrpos($url, 'alpha') === false && strrpos($url, 'local') === false) {
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
                <li style="float:right; margin-right: 30px"> 
                <a href="user/logout" onclick="this.blur();"><span><?=$words->get('Logout')?></span></a></li>
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
          <label for="login-u"><?=$ww->Username ?></label><br />
          <input type="text" id="login-u" name="u" />
          <br />
          <label for="login-p"><?=$ww->Password ?></label><br />
          <input type="password" id="login-p" name="p" />
          <br />
          <input type="submit" value="Login" id="smallbutton" class="button"/>
        </form>
        
        <script type="text/javascript">
            document.getElementById("login-u").focus();
        </script>
        
        </div>
        <?php } else { ?>
        <div class="info" id="login-widget">
        <h3><?=$ww->Login ?></h3>
        <form method="post" action="<?=$url ?>">
          <?=$callback_tag ?>
          <?=$mem_recovery_tag ?>
          <label for="login-u"><?=$ww->Username ?></label><br />
          <input type="text" id="login-u" name="u" />
          <br />
          <label for="login-p"><?=$ww->Password ?></label><br />
          <input type="password" id="login-p" name="p" />
          <br />
          <input type="submit" value="Login" id="smallbutton" class="button"/>
          <br />
          <p><?=$ww->LoginformForgetPassword('<a href="bw/lostpassword.php">', '</a>') ?></p>
          <h3><?=$ww->SignupNow ?></h3>
          <p><?=$ww->IndexPageWord17('<a href="signup">', '</a>') ?></p>
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
