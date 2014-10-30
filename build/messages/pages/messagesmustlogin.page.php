<?php
class MustloginPage extends MessagesBasePage
{
    private $_redirect_url = 'messages';

    // the address after login
    public function setRedirectURL($url)
    {
        $this->_redirect_url = $url;
    }

    protected function column_col3()
    {
        $url = htmlspecialchars($this->_redirect_url, ENT_QUOTES);
        ?><h3>Please log in!</h3>
        You tried to open<br>
        <a href="<?=$url ?>"><?=$url ?></a><br><br>
        which is only visible to logged-in members.<br>
        (anonymous people don't have a mailbox)<?php

        $login_widget = $this->createWidget('LoginFormWidget');

        if ($memory = $this->memory) {
            $login_widget->memory = $memory;
        }

        $login_widget->render();
    }
}
