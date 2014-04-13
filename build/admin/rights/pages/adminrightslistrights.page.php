<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminRightsListRightsPage extends AdminRightsBasePage
{
    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . "&raquo; <a href='admin/rights/list/rights'>{$this->words->get('AdminRightsListRights')}</a>";
    }

    public function getLateLoadScriptFiles() {
        $scripts = parent::getLateLoadScriptfiles();
        // $scripts[] = 'adminrightstooltip.js';
        return $scripts;
    }
}
