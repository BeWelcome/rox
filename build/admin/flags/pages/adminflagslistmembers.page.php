<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminFlagsListMembersPage extends AdminFlagsBasePage
{
    public function __construct($model = false) {
        parent::__construct($model);
        $this->setCurrent('AdminFlagsListMembers');
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . "&raquo; <a href='admin/flags/list/members'>{$this->words->get('AdminFlagsListMembers')}</a>";
    }

    public function getLateLoadScriptFiles() {
        $scripts = parent::getLateLoadScriptfiles();
//        $scripts[] = 'adminflagstooltip.js';
        return $scripts;
    }
}
