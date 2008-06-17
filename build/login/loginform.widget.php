<?php


class LoginFormWidget extends RoxWidget
{
    private $_memory = false;
    
    public function render()
    {
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $formkit = $layoutkit->formkit;
        
        $callback_tag = $formkit->setPostCallback('LoginController', 'loginCallback');
        $mem_recovery_tag = $formkit->setMemForRecovery();
        
        $req = implode('/', PRequest::get()->request);
        
        if (APP_User::loggedIn()) {
            // already logged in
            // why show this form?
            ?>
            <div style="border:1px solid grey;">
              <p>You are already logged in!</p>
              <p><a href="logout">logout</a></p>
            </div>
            <?php 
        } else {
            ?>
            <div class="info">
            <h3>Login</h3>
            <form method="post" action="<?=$req ?>">
              <?=$callback_tag ?>
              <?=$mem_recovery_tag ?>
              
              <p>
                <label for="login-u">Username</label>
                <input type="text" id="login-u" name="u" />
              </p>
              <p>
                <label for="login-p">Password</label>
                <input type="password" id="login-p" name="p" />
              </p>
              <p>
                <input type="submit" value="Login" class="button"/>
              </p>
              <p>Forgot your password? Get a new one <a href="lostpassword.php">here</a> !</p>
              <h3>Sign up now</h3>
              <p><a href="signup.php">Create a profile</a> without obligations. Joining and using the network is free!</p>
            </form>
            <script type="text/javascript">document.getElementById("login-u").focus();</script>
            </div>
            <?php
        }
    }
}


?>