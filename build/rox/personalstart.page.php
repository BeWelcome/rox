<?php


class PersonalStartpage extends RoxPageView
{
    protected function getTopmenuActiveItem() {
        return 'main';
    }

    protected function teaserContent()
    {
        $words = new MOD_words();
        $thumbPathMember = MOD_layoutbits::smallUserPic_userId($_SESSION['IdMember']);
        //$imagePathMember = MOD_user::getImage();
        
        $_newMessagesNumber = 5; // $this->_model->getNewMessagesNumber($_SESSION['IdMember']);
        
        if ($_newMessagesNumber > 0) {
            $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNewMessages', $_newMessagesNumber);
        } else {
            $_mainPageNewMessagesMessage = $words->getFormatted('MainPageNoNewMessages');
        }
        require TEMPLATE_DIR.'apps/rox/teaser_main.php';
    }
    
    protected function getPageTitle() {
        if (isset($_SESSION['Username'])) {
            return 'Welcome, '.$_SESSION['Username'];
        } else {
            // this should not happen actually!
            return 'Welcome, Guest!';
        }
    }
    
    protected function leftSidebar()
    {
        require TEMPLATE_DIR.'apps/rox/userbar.php';
    }
    
    protected function column_col3() {
        $Forums = new ForumsController;
        $citylatlong = $this->getModel()->getAllCityLatLong();
        $google_conf = PVars::getObj('config_google');  
        require TEMPLATE_DIR.'apps/rox/mainpage.php';
    }
}


?>