<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminRightsBasePage extends AdminBasePage
{
    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . " &raquo; <a href='admin/rights'>{$this->words->get('AdminRights')}</a>";
    }

}
