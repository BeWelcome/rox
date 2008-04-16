<?php


class AboutTheideaPage extends AboutBasePage
{
    protected function getPageTitle() {
        return 'About BeWelcome - The Idea *';
    }
    
    protected function getCurrentSubpage() {
        return 'theidea';
    }
    
    protected function column_col3() {
        require_once "magpierss/rss_fetch.inc";    
        require TEMPLATE_DIR.'apps/rox/about.php';
    }
}


?>