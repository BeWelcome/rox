<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminRightsAssignPage extends AdminRightsBasePage
{
    public function __construct($model = false) {
        parent::__construct();
        $this->setCurrent('AdminRightsAssign');
        $this->addLateLoadScriptFile('build/jquery_ui.js');
        $this->addLateLoadScriptFile('build/member/autocomplete.js');
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . " &raquo; <a href='admin/rights'>{$this->words->get('AdminRights')}</a>";
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'build/jquery_ui.css';
        return $stylesheets;
    }
}
