<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminFlagsOverviewPage extends AdminFlagsBasePage
{
    public function __construct($model = false) {
        parent::__construct($model);
        $this->setCurrent('AdminFlagsOverview');
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . "&raquo; <a href='admin/flags/list/member'>{$this->words->get('AdminFlagsOverview')}</a>";
    }
}
