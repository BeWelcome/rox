<?php


class PublicStartpage extends RoxPageView
{
    protected function getStylesheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/YAML/screen/custom/bw_basemod_2col.css';
        $stylesheets[] = 'styles/YAML/screen/custom/index.css';
        return $stylesheets;
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
            $redirect_url = implode('/', array_slice($request, 1)).'?'.$_SERVER['QUERY_STRING'];
        } else {
            $redirect_url = false;
        }
        $User = new UserController;
        $User->displayLoginForm($redirect_url);
    }
    
    protected function column_col3() {
        $flagList = $this->_buildFlagList();
        require TEMPLATE_DIR.'apps/rox/startpage.php';
    }
    
    protected function getColumnNames ()
    {
        return array('col2', 'col3');
    }
}



?>