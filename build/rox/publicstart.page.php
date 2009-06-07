<?php


class PublicStartpage extends RoxPageView
{
    
    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/index.css';
        return $stylesheets;
    }

    protected function teaserContent() {
        $request = PRequest::get()->request;
        if(!isset($request[0])) {
            $redirect_url = false;
            require 'templates/teaser.php';
        } else if ($request[0]=='login') {
            $redirect_url = implode('/', array_slice($request, 1));
            if (!empty($_SERVER['QUERY_STRING'])) {
                $redirect_url .= '?'.$_SERVER['QUERY_STRING'];
            }
            $login_widget = $this->createWidget('LoginFormWidget');
            $login_widget->render();
        } else {
            require 'templates/teaser.php';
        }
    }

    protected function getPageTitle() {
        $words = new MOD_words();
        if (isset($_SESSION['Username'])) {
            return $words->getFormatted('WelcomeUsername',$_SESSION['Username']);
        } else {
            // this should not happen actually!
            return $words->getFormatted('WelcomeGuest');
        }
    }

    protected function column_col3() {
        $members = $this->model->getMembersStartpage(2);
        $request = PRequest::get()->request;
        if(!isset($request[0])) {
            $redirect_url = false;
            require 'templates/startpage.php';
        } else if ($request[0]=='login') {
        } else {
            $redirect_url = false;
            require 'templates/startpage.php';
        }
    }

    protected function getColumnNames ()
    {
        return array('col3');
    }

    protected function quicksearch() {
        require 'templates/_languageselector.helper.php';
        $languageSelectorDropDown = _languageSelectorDropDown();
        $words = new MOD_words();
        echo $languageSelectorDropDown;
    }

}



?>
