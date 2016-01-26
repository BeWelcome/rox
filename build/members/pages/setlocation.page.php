<?php


class SetLocationPage extends PageWithRoxLayout
{
    public function __construct() {
        parent::__construct();
        $this->addLateLoadScriptFile('/jquery-ui-1.11.2/jquery-ui.js');
        $this->addLateLoadScriptFile('leaflet/1.0.0-master/leaflet.js');
        $this->addLateLoadScriptFile('signup/createmap.js');
        $this->addLateLoadScriptFile('search/searchlocation.js');
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = '/script/leaflet/1.0.0-master/leaflet.css';
        $stylesheets[] = '/script/jquery-ui-1.11.2/jquery-ui.css';
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
        if (isset($_SESSION['Username'])) {
            return 'Welcome, '.$_SESSION['Username'];
        } else {
            // this should not happen actually!
            return 'Welcome, Guest!';
        }
    }
}
