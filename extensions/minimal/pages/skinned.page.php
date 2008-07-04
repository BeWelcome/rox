<?php


class PageWithActiveSkin extends PageWithRoxLayout
{
    protected function body()
    {
        require 'templates/body_minimal.php';
    }

    protected function getStylesheets()
    {
        $stylesheets = '';
        $stylesheets[] = 'styles/guaka.css';
        return $stylesheets;
    }
    
    protected function topmenu()
    {
        $words = $this->getWords();
        $menu_items = $this->getTopmenuItems();
        $active_menu_item = $this->getTopmenuActiveItem();
        
        require 'templates/topmenu_minimal.php';
    }
    
    protected function footer()
    {
        $this->showTemplate('templates/footer_minimal.php', array(
            'flagList' => $this->_buildFlagList()
        ));
    }
    
    
}

class PageWithActiveSkinMinimal extends PageWithActiveSkin
{
    protected function teaserContent() {
        require 'templates/teaser_minimal.php';
    }
}

?>