<?php


class PageWithParameterizedRoxLayout extends PageWithActiveSkin
{
    protected function getPageTitle()
    {
        if ($title = $this->get('title')) {
            return $title;
        } else {
            return parent::getPageTitle();
        }
    }
    
    protected function includeStylesheets()
    {
        parent::includeStylesheets();
        if ($injected_styles = $this->get('addStyles')) {
            echo $injected_styles;
        }
    }
    
    protected function getTopmenuActiveItem()
    {
        return $this->get('currentTab');
    }
    
    protected function teaserContent()
    {
        echo $this->get('teaserBar');
    }
    
    protected function getSubmenuItems() {
        return false;
    }
    
    protected function submenu() {
        echo $this->get('subMenu');
    }
    
    protected function leftSidebar() {
        echo $this->get('newBar');
    }
    
    protected function column_col2() {
        echo $this->get('rContent');
    }
    
    protected function column_col3() {
        echo $this->get('content');
    }
}


?>