<?php


class LoginFormWidget extends RoxWidget
{
    private $_memory = false;
    
    public function render()
    {
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $ww = $this->ww;
        $formkit = $layoutkit->formkit;
        
        $callback_tag = $formkit->setPostCallback('LoginController', 'loginCallback');
        $mem_recovery_tag = $formkit->setMemForRecovery();
        
        $url = PVars::getObj('env')->baseuri . implode('/', PRequest::get()->request);
        if (!empty($_SERVER['QUERY_STRING'])) {
            $url .= '?'.$_SERVER['QUERY_STRING'];
        }
        
        $logged_in = APP_User::loggedIn();
        
        if ($logged_in) {
            // already logged in
            // why show this form?
            ?>
            <div style="border:1px solid grey;">
              <p><?=$ww->LoginformAlreadyLogged ?></p>
              <p><a href="logout"><?=$ww->Logout ?></a></p>
            </div>
            <?php
            // for translators, we want links for all the translations,
            // because otherwise they will never see the login form words.
            // we make this happen with a little trick :)
            ob_start();
            $ww = $this->wwsilent;
        }
        ?>
        <div class="info" id="login-widget">
        <h3><?=$ww->Login ?></h3>
        <form method="post" action="<?=$url ?>">
          <?=$callback_tag ?>
          <?=$mem_recovery_tag ?>
          
          <p>
            <label for="login-u"><?=$ww->Username ?></label>
            <input type="text" id="login-u" name="u" />
          </p>
          <p>
            <label for="login-p"><?=$ww->Password ?></label>
            <input type="password" id="login-p" name="p" />
          </p>
          <p>
            <input type="submit" value="Login" class="button"/>
          </p>
          <p><?=$ww->LoginformForgetPassword('<a href="lostpassword.php">', '</a>') ?> = Did you forget your password? Get a new one <a href="lostpassword.php">here</a>!</p>
          <h3><?=$ww->SignupNow ?></h3>
          <p><a href="signup.php"><?=$ww->IndexPageWord17 ?></p>
        </form>
        <script type="text/javascript">document.getElementById("login-u").focus();</script>
        </div>
        <?php
        if ($logged_in) {
            ob_end_clean();
            $words->flushBuffer();
        }
    }
}


?>