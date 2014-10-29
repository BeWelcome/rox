<?php
/**
 * Created by PhpStorm.
 * User: shevek
 * Date: 06.04.14
 * Time: 12:07
 */

class AdminNewMembersBasePage extends AdminBasePage
{
    protected $_statuses = false;
    protected $_current = false;

    public function __construct() {
        parent::__construct(new AdminNewMembersModel());
        $member = $this->model->getLoggedInMember();
        $this->_statuses = $this->model->getStatuses();
    }

    public function teaserHeadline()
    {
        $headline = parent::teaserHeadline();
        return $headline . " &raquo; <a href='admin/newmwmbers'>{$this->words->get('AdminNewMembers')}</a>";
    }

    /**
     * @param $current current item in the side bar
     */
    protected function setCurrent($current) {
        $this->_current = $current;
    }

    protected function getStylesheets()
    {
        $stylesheets = parent::getStylesheets();
        $stylesheets[] = 'styles/css/minimal/screen/custom/admin.css?1';
        return $stylesheets;
    }
}
