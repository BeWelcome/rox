<?php


class SignupFinishPage extends SignupBasePage
{
    
    protected function teaserHeadline()
    {
        $words = $this->layoutkit->words;
        echo $words->get('SignupFinishedTitle');
    }
    
    protected function column_col2()
    {
    }
    
    protected function column_col3()
    {
        $email = '(hidden e-mail address)';
        if (isset($_SESSION['SignupBWVars'])) {
            // we have vars still stored, delete them
            $email = $_SESSION['SignupBWVars']['email'];
            unset($_SESSION['SignupBWVars']);
        }
        
        $words = $this->layoutkit->words;
        
        if ($User = APP_User::login()) {
            // show the page anyway.
            // redirect should happen in the controller.
            // but for translators show the page.
            echo '
<div style="background:yellow; border:1px solid black; padding:10px; width: 44em; margin-bottom: 2em;">
<div style="font-size:160%;">
You can see the signup page because you are a translator.<br />
Normally you cannot see it when logged in.<br />
Please only use the page for translations!
</div>
<br />Related page: <a href="signup">Signup form</a>
</div>
'
            ;
        }
        require 'templates/finish.php';
    }
}
