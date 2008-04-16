<?php


class AboutThepeoplePage extends AboutBasePage
{
    protected function getPageTitle() {
        return 'About BeWelcome: The People *';
    }
    
    protected function getCurrentSubpage() {
        return 'thepeople';
    }
    
    protected function column_col3() {
        require TEMPLATE_DIR.'apps/rox/thepeople.php';
    }
}


?>