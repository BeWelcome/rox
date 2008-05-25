<?php


class PageWithActiveSkin extends PageWithRoxLayout
{
    protected function body()
    {
        require TEMPLATE_DIR . 'shared/ext_minimal/body_minimal.php';
    }

    protected function getStylesheets()
    {
        $stylesheets = '';
        $stylesheets[] = 'styles/minimal.css';
        return $stylesheets;
    }
    
    protected function topmenu()
    {
        $words = $this->getWords();
        $menu_items = $this->getTopmenuItems();
        $active_menu_item = $this->getTopmenuActiveItem();
        
        require TEMPLATE_DIR . 'shared/ext_minimal/topmenu_minimal.php';
    }
    
    protected function footer()
    {
        $this->showTemplate('shared/ext_minimal/footer_minimal.php', array(
            'flagList' => $this->_buildFlagList()
        ));
    }
    
    
}

class PageWithActiveSkinMinimal extends PageWithActiveSkin
{
    protected function teaserContent() {
        require TEMPLATE_DIR . 'shared/ext_minimal/teaser_minimal.php';
    }
}

?>