<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminFlagsAssignPage extends AdminFlagsBasePage
{
    public function __construct($model = false) {
        parent::__construct($model);
        $this->setCurrent('AdminFlagsAssign');
        $this->addLateLoadScriptFile('build/jquery_ui.js');
        $this->addLateLoadScriptFile('build/member/autocomplete.js');
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . "&raquo; <a href='admin/flags'>{$this->words->get('AdminFlags')}</a>";
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'build/jquery_ui.css';
        return $stylesheets;
    }
}
