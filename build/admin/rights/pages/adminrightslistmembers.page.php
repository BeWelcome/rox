<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminRightsListMembersPage extends AdminRightsBasePage
{
    public function __construct($model = false) {
        parent::__construct($model);
        $this->setCurrent('AdminRightsListMembers');
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . "&raquo; <a href='admin/rights/list/members'>{$this->words->get('AdminRightsListMembers')}</a>";
    }

    public function getLateLoadScriptFiles() {
        $scripts = parent::getLateLoadScriptfiles();
//        $scripts[] = 'adminrightstooltip.js';
        return $scripts;
    }
}
