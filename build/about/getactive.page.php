<?php


class AboutGetactivePage extends AboutBasePage
{
    protected function getPageTitle() {
        return 'About BeWelcome: Get Active *';
    }
    
    protected function getCurrentSubpage() {
        return 'getactive';
    }
    
    protected function column_col3() {
        require TEMPLATE_DIR.'apps/rox/getactive.php';
    }
}


?>