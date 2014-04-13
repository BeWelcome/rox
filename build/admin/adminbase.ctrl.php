<?php

class AdminBaseController extends RoxController {

    public function __construct()
    {
        parent::__construct();
        $this->_model = new AdminBaseModel();
    }

    public function __destruct()
    {
        unset($this->_model);
    }

    /**
     * redirects if the member has got no business
     * otherwise returns member entity and array of rights
     *
     * @access private
     * @return array
     */
    protected function checkRights($right = '')
    {
        if (!$member = $this->_model->getLoggedInMember()) {
            $this->redirectAbsolute($this->router->url('main_page'));
            exit(0);
        }
        $rights = $member->getOldRights();
        if (empty($rights) || (!empty($right) && !in_array($right, array_keys($rights)))) {
            $this->redirectAbsolute($this->router->url('admin_norights'));
            exit(0);
        }
        return array($member, $rights);
    }

    /**
     * displays message about not having any admin rights
     *
     * @access public
     * @return object
     */
    protected function noRights()
    {
        if (!$member = $this->_model->getLoggedInMember()) {
            $this->redirectAbsolute($this->router->url('main_page'));
        }
        $page = new AdminNoRightsPage;
        $page->member = $member;
        return $page;
    }


}