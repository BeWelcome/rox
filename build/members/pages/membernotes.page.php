<?php

class MemberNotesPage extends ProfilePage
{
    protected function getSubmenuActiveItem()
    {
        return 'groups';
    }
    
    protected function getStyleSheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] =  'styles/css/minimal/screen/custom/profilenotes.css';
        return $stylesheets; 
    }
}
