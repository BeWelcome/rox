<?php


class PublicStartpage extends RoxPageView
{
protected function topnav() {

    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/index.css?3';
        $stylesheets[] = 'styles/css/minimal/screen/custom/font-awesome.min.css';
        $stylesheets[] = 'styles/css/minimal/screen/custom/font-awesome-ie7.min.css';
        return $stylesheets;
    }

    protected function teaserContent() {
        $request = PRequest::get()->request;
        if(!isset($request[0])) {
            $redirect_url = false;
            require 'templates/teaser.php';
        } else {
            require 'templates/teaser.php';
        }
    }

    protected function getPageTitle() {
        $words = new MOD_words();
        if (isset($_SESSION['Username'])) {
            return $words->getFormatted('WelcomeUsername',$_SESSION['Username']);
        } else {
            return 'BeWelcome';
        }
    }

    protected function column_col3() {

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
