<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminSubscriptionsManagePage extends AdminBasePage
{
    protected $sidebar = array(
        'AdminFlagsAssign' => 'admin/flags/assign',
        'AdminFlagsOverview' => 'admin/flags/overview',
        'AdminFlagsListMembers' => 'admin/flags/list/members',
        'AdminFlagsListFlags' => 'admin/flags/list/flags',
        'AdminFlagsCreate' => 'admin/flags/create',
    );

    protected $current = false;
    protected $flags = false;
    protected $create = false;

    public function __construct() {
        parent::__construct(new AdminSubscriptionsModel());
        $member = $this->model->getLoggedInMember();
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . "&raquo; <a href='admin/subscriptions'>{$this->words->get('AdminManageSubscriptions')}</a>";
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
