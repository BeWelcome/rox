<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminRightsOverviewPage extends AdminRightsBasePage
{
    public function __construct($model = false) {
        parent::__construct($model);
        $this->setCurrent('AdminRightsOverview');
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . "&raquo; <a href='admin/rights/list/member'>{$this->words->get('AdminRightsOverview')}</a>";
    }
}
