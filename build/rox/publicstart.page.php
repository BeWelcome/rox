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
        ?>


            <?php
    }
    
    protected function teaserContent() {
        require TEMPLATE_DIR.'apps/rox/teaser.php';
    }
    
    protected function getPageTitle() {
        if (isset($_SESSION['Username'])) {
            return 'Welcome, '.$_SESSION['Username'].'!';
        } else {
            return 'Welcome, Guest!';
        }
    }
    
    protected function column_col1()
    {
        // should be invisible anyway
        echo 'left column';
    }
    
    protected function column_col2()
    {
        $request = PRequest::get()->request;
        if(!isset($request[0])) {
            $redirect_url = false;
        } else if ($request[0]=='login') {
            $redirect_url = implode('/', array_slice($request, 1));
            if (!empty($_SERVER['QUERY_STRING'])) {
                $redirect_url .= '?'.$_SERVER['QUERY_STRING'];
            }
        } else {
            $redirect_url = false;
        }
        
        /*
        $User = new UserController;
        $User->displayLoginForm($redirect_url);
        */
        
        $login_widget = $this->createWidget('LoginFormWidget');
        $login_widget->render();
    }
    
    protected function column_col3() {        
        $members = $this->model->getMembersStartpage(7);
        require 'templates/_languageselector.helper.php';
        require 'templates/startpage.php';
        require 'templates/startpage_people.php';
    }
    
    protected function getColumnNames ()
    {
        return array('col2', 'col3');
    }
    
    protected function quicksearch()
    {
        PPostHandler::setCallback('quicksearch_callbackId', 'SearchmembersController', 'index');
    }
    
    
}



?>