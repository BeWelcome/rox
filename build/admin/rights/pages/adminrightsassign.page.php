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
        parent::__construct($model);
        $this->setCurrent('AdminRightsAssign');
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . "&raquo; <a href='admin/rights'>{$this->words->get('AdminRights')}</a>";
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/jquery-ui/smoothness/jquery-ui-1.10.4.custom.min.css';
        return $stylesheets;
    }

    public function getLateLoadScriptFiles()
    {
        $scripts = parent::getLateLoadScriptfiles();
        $scripts[] = 'adminrightsajax.js';
        return $scripts;
    }
}
