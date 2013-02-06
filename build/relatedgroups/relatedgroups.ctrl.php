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
     * relatedgroups controller
     * handles all requests that have to do with related groups
     * 
     * @package    Apps
     * @subpackage RelatedGroups
     * @author     mahouni
     */
class RelatedGroupsController extends RoxControllerBase   
{

    public function __construct()
    {
        parent::__construct();
        $this->_model = new RelatedGroupsModel();
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
        if (!($vars = $this->route_vars) || empty($vars['group_id']) || !($group = $this->_model->findGroup($vars['group_id']))) {
            if (!$redirect) {
                $redirect = $this->router->url('groups_overview');
            }
            $this->redirectAbsolute($redirect);
        }
        return $group;
    }

    /**
     * fetches related group entity from route vars or redirects to a given route
     *
     * @param string $redirect url to redirect to
     *
     * @access private
     * @return object
     * TODO: Looks like this method is not in use. Remove?
     */
    private function _getRelatedGroupFromRequest($redirect = null)
    {
        if (!($vars = $this->route_vars) || empty($vars['related_id']) || !($relatedgroup = $this->_model->findGroup($vars['related_id']))) {
            if (!$redirect) {
                $redirect = $this->router->url('groups_overview');
            }
            $this->redirectAbsolute($redirect);
        }
        return $relatedgroup;
    }


    /**
     * handles member selecting a related group
     *
     * @access public
     * @return object $page
     */
    public function selectRelatedGroup()
    {
        $group = $this->_getGroupFromRequest();

        $page = new GroupAddRelatedGroupPage();
        $page->my_groups = $this->_model->getMyGroups($group);
        $page->group = $group;
        $page->member = $this->_model->getLoggedInMember();
        return $page;
    }



    /**
     * handles member adding a related group
     *
     * @return object $page
     */
    public function addRelatedGroup()
    {
        $member = $this->_model->getLoggedInMember();
        $group = $this->_getGroupFromRequest();
        $isGroupAdmin = $group->isGroupOwner($member);
        $relatedgroup = $this->_getRelatedGroupFromRequest();
        $result = $this->_model->memberAddsRelatedGroup($group->getPKValue(), $relatedgroup->getPKValue(), $member->getPKValue());
        if ($result) {
            $page = new GroupRelatedGroupLogPage();
            $page->group = $group;
            $page->member = $member;
            $page->isGroupAdmin = $isGroupAdmin;
            $page->logs = $this->_model->showRelatedGroupsLog($group->getPKValue());
            $this->setFlashNotice($this->getWords()->getFormatted("SuccessfullyAddedRelatedGroup", htmlspecialchars($relatedgroup->Name, ENT_QUOTES)));
         } else {
            $page = new GroupRelatedGroupLogPage();
            $page->group = $group;
            $page->member = $member;
            $page->isGroupAdmin = $isGroupAdmin;
            $page->logs = $this->_model->showRelatedGroupsLog($group->getPKValue());
            $this->setFlashError($this->getWords()->getFormatted("ErrorWhileAddingRelatedGroup", htmlspecialchars($relatedgroup->Name, ENT_QUOTES)));
         }
         return $page;
     }


    /**
     * handles member selecting a related group to be removed
     *
     * @access public
     * @return object $page
     */
    public function selectdeleteRelatedGroup()
    {
        $group = $this->_getGroupFromRequest();

        $page = new GroupDeleteRelatedGroupPage();
        $page->group = $group;
        $page->relatedgroups =  $group->findRelatedGroups($group->getPKValue());
        $page->member = $this->_model->getLoggedInMember();
        return $page;
    }


    /**
     * handles member deleting a related group
     *
     * @access public
     * @return object $page
     */
    public function deleteRelatedGroup()
    {
        $member = $this->_model->getLoggedInMember();
        $group = $this->_getGroupFromRequest();
        $isGroupAdmin = $group->isGroupOwner($member);
        $relatedgroup = $this->_getRelatedGroupFromRequest();
        $result = $this->_model->memberDeletesRelatedGroup($group->getPKValue(), $relatedgroup->getPKValue(), $member->getPKValue());
        if ($result) {
            $page = new GroupRelatedGroupLogPage();
            $page->group = $group;
            $page->member = $member;
            $page->isGroupAdmin = $isGroupAdmin;
            $page->logs = $this->_model->showRelatedGroupsLog($group->getPKValue());
            $this->setFlashNotice($this->getWords()->getFormatted('SuccessfullyRemovedRelatedGroup', htmlspecialchars($relatedgroup->Name, ENT_QUOTES)));
            return $page;
         } else {
            $page = new GroupRelatedGroupLogPage();
            $page->group = $group;
            $page->member = $member;
            $page->isGroupAdmin = $isGroupAdmin;
            $page->logs = $this->_model->showRelatedGroupsLog($group->getPKValue());
            $this->setFlashError($this->getWords()->getFormatted('ErrorWhileRemoveRelatedGroup', htmlspecialchars($relatedgroup->Name, ENT_QUOTES)));
            return $page;
         }
     }


    /**
     * shows the activities of adding and deleting related groups
     *
     * @access public
     * @return object $page
     */
    public function showRelatedGroupLog()
    {
        $member = $this->_model->getLoggedInMember();
        $group = $this->_getGroupFromRequest();
        $isGroupAdmin = $group->isGroupOwner($member);
        $page = new GroupRelatedGroupLogPage();
        $page->group = $group;
        $page->member = $member;
        $page->isGroupAdmin = $isGroupAdmin;
        $page->logs = $this->_model->showRelatedGroupsLog($group->getPKValue());
        return $page;
    }


}

