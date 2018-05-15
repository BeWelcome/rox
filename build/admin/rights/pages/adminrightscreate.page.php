<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminRightsCreatePage extends AdminRightsBasePage
{
    public function __construct($model = false) {
        parent::__construct($model);
        $this->setCurrent('AdminRightsAssign');
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . " &raquo; <a href='admin/rights'>{$this->words->get('AdminRights')}</a>";
    }
}
