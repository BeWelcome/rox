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
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . "&raquo; <a href='admin/flags'>{$this->words->get('AdminFlags')}</a>";
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
        $scripts[] = 'adminflagsajax.js';
        return $scripts;
    }
}
