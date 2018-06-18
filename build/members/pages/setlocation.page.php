<?php


class SetLocationPage extends PageWithRoxLayout
{
    public function __construct() {
        parent::__construct();
        $this->addLateLoadScriptFile('build/jquery_ui.js');
        $this->addLateLoadScriptFile('build/leaflet.js');
        $this->addLateLoadScriptFile('script/signup/createmap.js');
        $this->addLateLoadScriptFile('script/search/searchlocation.js');
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'build/leaflet.css';
        return $stylesheets;
    }

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

    protected function getColumnNames()
    {
        return ['col3'];
    }

    protected function getPageTitle() {
        if ($this->_session->has( 'Username' )) {
            return 'Welcome, '.$this->_session->get('Username');
        } else {
            // this should not happen actually!
            return 'Welcome, Guest!';
        }
    }
}
