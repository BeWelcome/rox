<?php


class PublicStartpage extends RoxPageView
{
    protected function body()
    {
        require TEMPLATE_DIR . 'shared/roxpage/body_index.php';
    }
    
    protected function getStylesheets() {
        $stylesheets[] = 'styles/minimal_index.css';
        return $stylesheets;
    }
    
    protected function getStylesheetPatches()
    {
        $stylesheet_patches[] = 'styles/YAML/patches/patch_2col_left_seo.css';
        return $stylesheet_patches;
    }
    
    protected function includeScriptfiles()
    {
        $stylesheets = parent::includeScriptfiles();
    }
    
    protected function teaserContent() {
        $request = PRequest::get()->request;
        if(!isset($request[0])) {
            $redirect_url = false;
            require TEMPLATE_DIR.'apps/rox/teaser.php';
        } else if ($request[0]=='login') {
            $redirect_url = implode('/', array_slice($request, 1));
            if (!empty($_SERVER['QUERY_STRING'])) {
                $redirect_url .= '?'.$_SERVER['QUERY_STRING'];
            }
            $login_widget = $this->createWidget('LoginFormWidget');
            $login_widget->render();
        } else {
            require TEMPLATE_DIR.'apps/rox/teaser.php';
        }
    }
    
    protected function getPageTitle() {
        if (isset($_SESSION['Username'])) {
            return 'Welcome, '.$_SESSION['Username'].'!';
        } else {
            return 'Be Welcome!';
        }
    }
    
    protected function column_col1()
    {
        // should be invisible anyway
        echo 'left column';
    }
    
    protected function column_col2()
    {

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
    
    protected function topnav() {
        require 'templates/_languageselector.helper.php';
        $languageSelectorDropDown = _languageSelectorDropDown();
        $words = new MOD_words();
        echo '<div class="grey">'.$languageSelectorDropDown.'</div>';
    }
    protected function quicksearch() {
        $login_widget = $this->createWidget('LoginFormWidget');
        $login_widget->render(true);
    }
    
}



?>
