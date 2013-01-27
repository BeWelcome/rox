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
     * subgroups controller
     * handles all requests that have to do with subgroups
     * 
     * @package    Apps
     * @subpackage Subgroups
     * @author     mahouni
     */
class SubgroupsController extends RoxControllerBase   
{

    public function __construct()
    {
        parent::__construct();
        $this->_model = new SubgroupsModel();
    }
    
    public function __destruct()
    {
        unset($this->_model);
    }



    /**
     * fetches group entity from route vars or redirects to a given route
     *
     * @param string $redirect url to redirect to
     *
     * @access private
     * @return object
     */
    private function _getGroupFromRequest($redirect = null)
    {
        if (!($vars = $this->route_vars) || empty($vars['group_id']) || !($group = $this->_model->findGroup($vars['group_id'])))
        {
            if (!$redirect)
            {
                $redirect = $this->router->url('groups_overview');
            }
            $this->redirectAbsolute($redirect);
        }
        return $group;
    }

    /**
     * fetches subgroup entity from route vars or redirects to a given route
     *
     * @param string $redirect url to redirect to
     *
     * @access private
     * @return object
     */
    private function _getSubGroupFromRequest($redirect = null)
    {
        if (!($vars = $this->route_vars) || empty($vars['subgroup_id']) || !($subgroup = $this->_model->findGroup($vars['subgroup_id'])))
        {
            if (!$redirect)
            {
                $redirect = $this->router->url('groups_overview');
            }
            $this->redirectAbsolute($redirect);
        }
        return $subgroup;
    }


    /**
     * handles member selecting a subgroup
     *
     * @access public
     * @return object $page
     */
    public function selectSubgroup()
    {
        $group = $this->_getGroupFromRequest();

        $page = new GroupAddSubgroupPage();
        $page->my_groups = $this->_model->getMyGroups($group);
        $page->group = $group;
        $page->member = $this->_model->getLoggedInMember();
        return $page;
    }



    /**
     * handles member adding a subgroup
     *
     * @return object $page
     */
    public function addSubgroup()
    {
        $member = $this->_model->getLoggedInMember();
        $group = $this->_getGroupFromRequest();
        $isGroupAdmin = $group->isGroupOwner($member);
        $subgroup = $this->_getSubgroupFromRequest();
        $result = $this->_model->MemberAddsSubgroup($group->getPKValue(), $subgroup->getPKValue(), $member->getPKValue());
        if ($result)
        {
            $page = new GroupSubgroupLogPage();
            $page->group = $group;
            $page->member = $member;
            $page->isGroupAdmin = $isGroupAdmin;
            $page->logs = $this->_model->showSubgroupsLog($group->getPKValue());
            $this->setFlashNotice($this->getWords()->getFormatted("SuccessfullyAddedSubgroup", htmlspecialchars($subgroup->Name, ENT_QUOTES)));
         } else {
            $page = new GroupSubgroupLogPage();
            $page->group = $group;
            $page->member = $member;
            $page->isGroupAdmin = $isGroupAdmin;
            $page->logs = $this->_model->showSubgroupsLog($group->getPKValue());
            $this->setFlashError($this->getWords()->getFormatted("ErrorWhileAddingSubgroup", htmlspecialchars($subgroup->Name, ENT_QUOTES)));
         }
         return $page;
     }


    /**
     * handles member selecting a subgroup to be removed
     *
     * @access public
     * @return object $page
     */
    public function selectdeleteSubgroup()
    {
        $group = $this->_getGroupFromRequest();

        $page = new GroupDeleteSubgroupPage();
        $page->group = $group;
        $page->subgroups =  $group->findSubgroups($group->getPKValue());
        $page->member = $this->_model->getLoggedInMember();
        return $page;
    }


    /**
     * handles member deleting a subgroup
     *
     * @access public
     * @return object $page
     */
    public function deleteSubgroup()
    {
        $member = $this->_model->getLoggedInMember();
        $group = $this->_getGroupFromRequest();
        $isGroupAdmin = $group->isGroupOwner($member);
        $subgroup = $this->_getSubgroupFromRequest();
        $result = $this->_model->MemberDeletesSubgroup($group->getPKValue(), $subgroup->getPKValue(), $member->getPKValue());
        if ($result)
        {
            $page = new GroupSubgroupLogPage();
            $page->group = $group;
            $page->member = $member;
            $page->isGroupAdmin = $isGroupAdmin;
            $page->logs = $this->_model->showSubgroupsLog($group->getPKValue());
            $this->setFlashNotice($this->getWords()->SuccessfullyRemovedSubgroup . " " . htmlspecialchars($subgroup->Name, ENT_QUOTES));
            return $page;
         } else {
            $page = new GroupSubgroupLogPage();
            $page->group = $group;
            $page->member = $member;
            $page->isGroupAdmin = $isGroupAdmin;
            $page->logs = $this->_model->showSubgroupsLog($group->getPKValue());
            $this->setFlashError($this->getWords()->ErrorWhileRemoveSubgroup . " " . htmlspecialchars($subgroup->Name, ENT_QUOTES));
            return $page;
         }
     }


    /**
     * shows the activities of adding and deleting subgroups
     *
     * @access public
     * @return object $page
     */
    public function showSubgroupLog()
    {
        $member = $this->_model->getLoggedInMember();
        $group = $this->_getGroupFromRequest();
        $isGroupAdmin = $group->isGroupOwner($member);
        $page = new GroupSubgroupLogPage();
        $page->group = $group;
        $page->member = $member;
        $page->isGroupAdmin = $isGroupAdmin;
        $page->logs = $this->_model->showSubgroupsLog($group->getPKValue());
        return $page;
    }


}

