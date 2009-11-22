<?php
/*

Copyright (c) 2007-2009 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/

    /**
     * @author Felix <fvanhove@gmx.de>
     * @author Fake51
     */

    /**
     * admin controller
     * deals with actions that are available exclusively for administrators
     * 
     * @package apps
     * @subpackage Admin
     */
class AdminController extends RoxControllerBase
{

    private $_model;
    
    public function __construct()
    {        
        parent::__construct();
        $this->_model = new AdminModel();
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
    private function checkRights($right = '')
    {
        if (!$member = $this->_model->getLoggedInMember())
        {
            $this->redirectAbsolute($this->router->url('main_page'));
        }
        $rights = $member->getOldRights();
        if (empty($rights) || (!empty($right) && !in_array($right, array_keys($rights))))
        {
            $this->redirectAbsolute($this->router->url('admin_norights'));
        }
        return array($member, $rights);
    }

    /**
     * displays message about not having any admin rights
     *
     * @access public
     * @return object
     */
    public function noRights()
    {
        if (!$member = $this->_model->getLoggedInMember())
        {
            $this->redirectAbsolute($this->router->url('main_page'));
        }
        $page = new AdminNoRightsPage;
        $page->member = $member;
        return $page;
    }

    /**
     * overview page, displays tools for admins
     *
     * @access public
     * @return object
     */
    public function index()
    {
        list($member, $rights) = $this->checkRights();
        $page = new AdminOverviewPage;
        $page->member = $member;
        $page->rights = $rights;
        return $page;
    }

//{{{ Debug right methods
    /**
     * displays the php error logs
     *
     * @access public
     * @return object
     */
    public function debugLogs()
    {
        list($member, $rights) = $this->checkRights('Debug');
        $page = new AdminLogsPage($this->route_vars['log_type']);
        $page->member = $member;
        $page->rights = $rights;
        $page->lines = ((!empty($this->args_vars->get['lines']) && intval($this->args_vars->get['lines'])) ? $this->args_vars->get['lines'] : 100);
        return $page;
    }

//}}} Debug right methods

//{{{ Accepter right methods
    public function accepter()
    {
        list($member, $rights) = $this->checkRights('Accepter');
        $page = new AdminAccepterPage;
        $page->member = $member;
        $page->scope = explode(',', str_replace('"', '', $rights['Accepter']['Scope']));
        $page->status = ((!empty($this->args_vars->get['status'])) ? $this->args_vars->get['status'] : 'Pending');

        $params->strategy = new HalfPagePager('left');
        $params->items = $this->_model->countMembersWithStatus($page->status);
        $params->items_per_page = 25; 
        $page->pager = new PagerWidget($params);
        $page->members = $this->_model->getMembersWithStatus($page->status, $page->pager);
        $page->members_count = $page->pager->getTotalCount();
        $page->model = $this->_model;
        return $page;
    }

//}}}

    /**
     * comments overview method
     *
     * @access public
     * @return object
     */
    public function commentsOverview()
    {
        list($member, $rights) = $this->checkRights('Comments');
        $page = new AdminCommentsPage;
        $page->member = $member;

        $page->bad_comments = $this->_model->getBadComments();
        $params->strategy = new HalfPagePager('left');
        $params->items = count($page->bad_comments);
        $params->items_per_page = 25; 
        $page->pager = new PagerWidget($params);
        return $page;
    }


    public function activityLogs()
    {
/*
                case 'activitylogs':

                $level = $R->hasRight('Logs');
	            if (!$level || $level < 1) {
	                PPHP::PExit(); // TODO: redirect or display message?
	            }

	            ob_start();
	            $this->_view->leftSidebar();
	            $this->_view->activitylogs($level);
	            $str = ob_get_contents();
	            ob_end_clean();
	            $Page = PVars::getObj('page');
	            $Page->content .= $str;
	            break;
*/

    }

    public function wordsDownload()
    {
                $level = $R->hasRight('Words');
                if (!$level || $level < 1) {
                    PPHP::PExit(); // TODO: redirect or display message?
                }

                ob_start();
                $this->_view->wordsdownload($this->_model->wordsdownload());
                $str = ob_get_contents();
                ob_end_clean();
                $Page = PVars::getObj('page');
                $Page->content .= $str;

                ob_start();
                $this->_view->wordsdownload_teaser();
                $str = ob_get_contents();
                $Page->teaserBar .= $str;
                ob_end_clean();

    }
}
