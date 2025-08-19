<?php

class MemberNotesPage extends ProfilePage
{
    #[\Override]
    protected function getSubmenuActiveItem()
    {
        return 'groups';
    }
    
    #[\Override]
    protected function getStyleSheets() {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] =  'styles/css/minimal/screen/custom/profilenotes.css';
        return $stylesheets; 
    }
}
