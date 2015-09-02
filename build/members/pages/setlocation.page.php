<?php


class SetLocationPage extends PageWithRoxLayout
{
    protected function body()
    {
        require TEMPLATE_DIR . 'shared/roxpage/body.php';
    }

    protected function getStylesheetPatches()
    {
        parent::getStylesheetPatches();
    }

    protected function getTopmenuActiveItem() {
        return 'myaccount';
    }

    protected function teaserContent()
    {
        parent::teaserContent();
    }
    
    protected function teaserHeadline()
    {
        $words = new MOD_words();
        return $words->get('SetLocation');
    }

    protected function leftSidebar()
    {
        
    }
    
    protected function getPageTitle() {
        if (isset($_SESSION['Username'])) {
            return 'Welcome, '.$_SESSION['Username'];
        } else {
            // this should not happen actually!
            return 'Welcome, Guest!';
        }
    }
}
