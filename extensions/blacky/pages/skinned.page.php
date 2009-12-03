<?php


class PageWithActiveSkin extends PageWithRoxLayout
{
    protected function body()
    {
        require 'templates/body_blacky.php';
    }

    protected function getStylesheets()
    {
        $stylesheets = '';
        $stylesheets[] = 'styles/css/blacky/blacky.css';
        return $stylesheets;
    }
    
    // protected function footer()
    // {
        // $this->showTemplate('templates/footer_blacky.php', array(
            // 'flagList' => $this->_buildFlagList()
        // ));
    // }
    
    // protected function teaserContent() {
    //     require 'templates/teaser_blacky.php';
    // }
    
    
}

class PageWithActiveSkinblacky extends PageWithActiveSkin
{
    // protected function teaserContent() {
    //     require 'templates/teaser_blacky.php';
    // }
}

?>