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
     * @author Fake51
     */

use App\Doctrine\GroupType;
use App\Entity\Group;

/**
 * groups controller
 * handles all requests that have to do with groups
 *
 * @property GroupsModel _model
 *
 * @package    Apps
 * @subpackage Groups
 * @author     Fake51 <peter.e.lind@gmail.com>
 */
class GroupsController extends RoxControllerBase
{

    public function __construct()
    {
        parent::__construct();
        $this->_model = new GroupsModel();
    }

    public function __destruct()
    {
        unset($this->_model);
    }

    /**
     * displays a group overview page for a group
     *
     * @access public
     * @return object
     */
    public function showGroup()
    {
        $member = $this->_model->getLoggedInMember();
        $group = $this->_getGroupFromRequest();
        if (false === $group || ($group->Type == GroupType::INVITE_ONLY && !$this->_model->getLoggedInMember()))
        {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        }
        if (($group->approved <> 1) && !$group->isGroupAdmin($member)) {
            $this->setFlashNotice($this->_model->getWords()->get('group.not.approved.yet'));
            return $this->redirectAbsolute('/groups/search');
        }
        $this->_model->setGroupVisit($group->getPKValue());
        $page = new GroupStartPage();
        $page->setTeaserHeadline($group->Name);
        $page->group = $group;
        $this->_fillObject($page);
        return $page;
    }

    /**
     * fills page object with general vars
     *
     * @param object $page page object
     *
     * @access private
     * @return void
     */
    private function _fillObject($page)
    {
        $page->model = $this->_model;
        $page->member = $this->_model->getLoggedInMember();
    }

    /**
     * fetches member id from route vars or redirects to a given route
     *
     * @param string $redirect url to redirect to
     *
     * @access private
     * @return object
     */
    private function _getMemberIdFromRequest($redirect = null)
    {
        if (!($vars = $this->route_vars) || empty($vars['member_id']))
        {
            if (!$redirect)
            {
                $redirect = $this->router->url('groups_redirect');
            }
            $this->redirectAbsolute($redirect);
        }
        return $vars['member_id'];
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
                $redirect = $this->router->url('groups_redirect');
            }
            $this->redirectAbsolute($redirect);
        }
        return $group;
    }

    /**
     * main page of groups app
     *
     * @access public
     * @return object
     */
    public function index()
    {
        $page = new GroupsOverviewPage();
        $page->featured_groups = $this->_model->findAllGroups(0, 5);
        $page->my_groups = $this->_model->getMyGroups();
        $this->_fillObject($page);
        return $page;
    }

    /**
     * sets the proper header for outputting a binary image and sends the image
     * shows a thumbnail
     *
     * @access public
     * @return void
     */
    public function thumbImg()
    {
        PRequest::ignoreCurrentRequest();
        $vars = $this->route_vars;
        if (empty($vars['group_id']))
        {
            PPHP::PExit();
        }
        $this->_model->thumbImg($vars['group_id']);
        exit;
    }

    /**
     * sets the proper header for outputting a binary image and sends the image
     * shows a proper sized image
     *
     * @access public
     * @return void
     */
    public function realImg()
    {
        PRequest::ignoreCurrentRequest();
        $vars = $this->route_vars;
        if (empty($vars['group_id']))
        {
            PPHP::PExit();
        }
        $this->_model->realImg($vars['group_id']);
        exit;
    }

    /**
     *  searches for groups using GET vars
     *
     * @access public
     * @return object
     */
    public function search()
    {
        $terms = ((!empty($this->args_vars->get['GroupsSearchInput'])) ? $this->args_vars->get['GroupsSearchInput'] : '');
        if ($terms == '') {
            $order = ((!empty($this->args_vars->get['order'])) ? $this->args_vars->get['order'] : 'nameasc');
            $terms_array = [];
        } else {
            $order = ((!empty($this->args_vars->get['order'])) ? $this->args_vars->get['order'] : 'nameasc');
            $terms_array = explode(' ', $terms);
        }
        $params = new stdClass();
        $params->strategy = new FullPagePager('right');
        $params->items = $this->_model->countGroupsBySearchterms($terms_array);
        $params->items_per_page = 30;
        $pager = new PagerWidget($params);
        $page = new GroupsSearchPage();
        $page->search_result = $this->_model->findGroups($terms_array, $pager->active_page, $order, $pager->items_per_page);
        $page->result_order = $order;
        $page->search_terms = $terms;
        $page->pager = $pager;
        $this->_fillObject($page);
        return $page;
    }

    /**
     * fetches the groups for the logged in member and shows them
     *
     * @access public
     * @return object
     */
    public function myGroups()
    {
        if (!$this->_model->getLoggedInMember())
        {
            $this->redirectToLogin($this->router->url('groups_mygroups', null, false));
        }

        $page = new GroupsMyGroupsPage();
        $params = new stdClass;
        $params->strategy = new HalfPagePager('left');
        $params->items = $this->_model->countMyGroups();
        $params->items_per_page = 30;
        $pager = new PagerWidget($params);
        $page->search_result = $this->_model->getMyGroups();
        $page->pager = $pager;
        $this->_fillObject($page);
        return $page;
    }

    /**
     * fetches featured groups and shows them
     *
     * @access public
     * @return object
     */
    public function featured()
    {
        $page = new GroupsFeaturedPage();
        $order = ((!empty($this->args_vars->get['order'])) ? $this->args_vars->get['order'] : 'nameasc');
        $params = new stdClass;
        $params->strategy = new HalfPagePager('left');
        $params->items = $this->_model->countGroupsBySearchterms();
        $params->items_per_page = 20;
        $pager = new PagerWidget($params);
        $page->search_result = $this->_model->findGroups([], $pager->active_page, $order, $pager->items_per_page);
        $page->pager = $pager;
        $page->result_order = $order;
        $this->_fillObject($page);
        return $page;
    }


    //{{{ group action functions

    /**
     * displays a page allowing a group owner to invite members to the group
     *
     * @access public
     * @return object
     */
    public function inviteMembers()
    {
        $group = $this->_getGroupFromRequest($this->router->url('groups_redirect'));
        $args = $this->args_vars;
        if (empty($args->get['username']))
        {
            $this->redirectAbsolute($this->router->url('group_start', array('group_id' => $group->getPKValue())));
        }
        $members = $this->_model->findMembersByName($group, $args->get['username']);
        $page = new GroupInvitePage;
        $page->search_result = $members;
        $this->_fillObject($page);
        $page->group = $group;
        return $page;
    }

    /**
     * no-js method for inviting a member
     *
     * @access public
     * @return object
     */
    public function inviteMember()
    {
        $member_id = $this->_getMemberIdFromRequest();
        $group = $this->_getGroupFromRequest();
        $invitedby = $this->_model->getLoggedInMember();
        if ($group->Status != 'Public' && !$group->isGroupOwner($invitedby)) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } else {
            $page = new GroupStartPage;
            if ($this->_model->inviteMember($group, $member_id)) {
                $this->_model->sendInvitation($group, $member_id, $this->_model->getLoggedInMember());
                $page->memberinvited = true;
                $this->logWrite("Member #{$member_id} was invited to group #{$group->getPKValue()} by member #{$this->_model->getLoggedInMember()->getPKValue()}");
            } else {
                $page->memberinvited = false;
            }
        }
        $this->_fillObject($page);
        $page->group = $group;
        return $page;
    }

    /**
     * ajax method for searching for members to send group invites to
     *
     * @access public
     * @return object
     */
    public function memberSearchAjax()
    {
        header('Content-Type: text/plain; encoding=utf-8');
        if (empty($this->route_vars['search_term']) || empty($this->route_vars['group_id']) || !($group = $this->_model->findGroup($this->route_vars['group_id'])))
        {
            header('Status: 500 Fudged it');
            exit;
        }
        $invitedby = $this->_model->getLoggedInMember();
        if ( !$invitedby || ($group->Status != 'Public' && !$group->isGroupOwner($invitedby)))
        {
            header('Status: 500 Fudged it');
            exit;
        }
        $members = $this->_model->findMembersByName($group, $this->route_vars['search_term']);
        $output = array();
        foreach ($members as $member)
        {
            $output[$member->Username] = $member->getPKValue();
        }
        echo json_encode($output);
        exit;
    }

    /**
     * ajax method for inviting a member
     *
     * @access public
     * @return object
     */
    public function inviteMemberAjax()
    {
        header('Content-Type: text/plain; encoding=utf-8');
        $vars = $this->route_vars;
        if (!empty($vars['member_id']) && !empty($vars['group_id']) && ($group = $this->_model->findGroup($vars['group_id'])))
        {
            $invitedby = $this->_model->getLoggedInMember();
            if (!($group->Status != 'Public' && !$group->isGroupOwner($invitedby)) && $this->_model->inviteMember($group, $vars['member_id']))
            {
                $this->logWrite("Member #{$vars['member_id']} was invited to group #{$group->getPKValue()} by member #{$this->_model->getLoggedInMember()->getPKValue()}");
                $this->_model->sendInvitation($group, $vars['member_id'], $this->_model->getLoggedInMember());
                echo "success";
            }
            else
            {
                echo "fail";
            }
            exit;
        }
        else
        {
            header('Status: 500 Fudged it');
            exit;
        }
    }

    /**
     * called when a member accepts an invitation to join a group
     *
     * @access public
     * @return object
     */
    public function acceptInvitation()
    {
        $group = $this->_getGroupFromRequest();
        $member_id = $this->_getMemberIdFromRequest();
        if (!$this->_model->getLoggedInMember() ) {
            $this->redirectToLogin($this->router->url('group_acceptinvitation', array('group_id' => $group->getPKValue(), 'member_id' => $member_id), false));
        } elseif ($this->_model->getLoggedInMember()->getPkValue() != $member_id
                  || !$this->_model->memberAcceptedInvitation($group, $member_id)) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } else {
            $this->logWrite("Member #{$member_id} accepted invitation to join group #{$group->getPKValue()}");
            $page = new GroupMemberSettingsPage($group);
            $this->setFlashNotice($this->getWords()->getSilent('GroupJoinSuccess'));
            $this->_fillObject($page);
            return $page;
        }
    }

    /**
     * called when a member accepts an invitation to join a group
     *
     * @access public
     * @return void
     */
    public function declineInvitation()
    {
        $group = $this->_getGroupFromRequest();
        $member_id = $this->_getMemberIdFromRequest();
        if ($this->_model->getLoggedInMember())
        {
            $this->_model->memberDeclinedInvitation($group, $member_id);
            $this->logWrite("Member #{$member_id} declined invitation to join group #{$group->getPKValue()}");
        } else {
            $this->redirectToLogin($this->router->url('group_declineinvitation', array('group_id' => $group->getPKValue(), 'member_id' => $member_id), false));
        }
        $this->redirectAbsolute($this->router->url('groups_redirect'));
        $this->setFlashNotice($this->getWords()->getSilent('GroupDeclineSuccess'));
    }

    /**
     * bans a member from a group, so they can't join up again
     *
     * @access public
     * @return object $page
     */
    public function banMember()
    {
        $group = $this->_getGroupFromRequest();
        $member_id = $this->_getMemberIdFromRequest();
        if (!$this->_model->getLoggedInMember()) {
            $this->redirectToLogin($this->router->url('group_banmember', array('group_id' => $group->getPKValue(), 'member_id' => $member_id), false));
        } elseif (!$this->_model->canAccessGroupAdmin($group) || empty($member_id)) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } else {
            $banned = $this->_model->banGroupMember($group, $member_id, true);
            if ($banned){
                $this->setFlashNotice($this->getWords()->getSilent('GroupsMemberBanSuccess'));
                $this->logWrite("Member #{$member_id} was banned from group #{$group->getPKValue()} by member #{$this->_model->getLoggedInMember()->getPKValue()}");
            } else {
                $this->setFlashError($this->getWords()->getSilent('GroupsMemberBanFail'));
            }
            $this->redirectAbsolute($this->router->url('group_memberadministration',array('group_id' => $group->getPKValue())));
        }
    }

    /**
     * kicks a member from a group, by just taking them out of the group
     *
     * @access public
     * @return object $page
     */
    public function kickMember()
    {
        $group = $this->_getGroupFromRequest();
        $member_id = $this->_getMemberIdFromRequest();
        if (!$this->_model->getLoggedInMember()) {
            $this->redirectToLogin($this->router->url('group_kickmember', array('group_id' => $group->getPKValue(), 'member_id' => $member_id), false));
        } elseif (!$this->_model->canAccessGroupAdmin($group) || empty($member_id)) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } else {
            $kicked = $this->_model->banGroupMember($group, $member_id, false);
            if ($kicked){
                $this->setFlashNotice($this->getWords()->getSilent('MemberKickSuccess'));
                $this->logWrite("Member #{$member_id} was kicked from group #{$group->getPKValue()} by member #{$this->_model->getLoggedInMember()->getPKValue()}");
            } else {
                $this->setFlashError($this->getWords()->getSilent('MemberKickFail'));
            }
            $this->redirectAbsolute($this->router->url('group_memberadministration',array('group_id' => $group->getPKValue())));
        }
    }

    /**
     * declines member request to join a group, by just taking them out of the group
     *
     * @access public
     * @return object $page
     */
    public function declineMember()
    {
        $group = $this->_getGroupFromRequest();
        $member_id = $this->_getMemberIdFromRequest();
        if (!$this->_model->getLoggedInMember()) {
            $this->redirectToLogin($this->router->url('group_declinemember', array('group_id' => $group->getPKValue(), 'member_id' => $member_id), false));
        } elseif (!$this->_model->canAccessGroupAdmin($group) || empty($member_id)) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } else {
            $declined = $this->_model->banGroupMember($group, $member_id, false);
            if ($declined){
                $this->setFlashNotice($this->getWords()->getSilent('GroupsMemberDeclineSuccess'));
                $this->logWrite("Member #{$member_id} was declined access to group #{$group->getPKValue()} by member #{$this->_model->getLoggedInMember()->getPKValue()}");
            } else {
                $this->setFlashError($this->getWords()->getSilent('GroupsMemberDeclineFail'));
            }
            $this->redirectAbsolute($this->router->url('group_memberadministration',array('group_id' => $group->getPKValue())));
        }
    }


    /**
     * adds a member of a group as admin
     *
     * @access public
     * @return object $page
     */
    public function addMemberAsAdmin()
    {
        $group = $this->_getGroupFromRequest();
        $member_id = $this->_getMemberIdFromRequest();
        if (!$this->_model->getLoggedInMember()) {
            $this->redirectToLogin($this->router->url('group_addadmin', array('group_id' => $group->getPKValue(), 'member_id' => $member_id), false));
        } elseif (!$this->_model->canAccessGroupAdmin($group) || empty($member_id)) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } else {
            $newAdmin = $this->_model->addGroupMemberAsAdmin($group, $member_id);
            if ($newAdmin)
            {
                $this->logWrite("Member #{$member_id} added as admin to the group #{$group->getPKValue()} by member #{$this->_model->getLoggedInMember()->getPKValue()}");
                $this->setFlashNotice($this->getWords()->getSilent('GroupNewAdminSuccess'));
            } else {
                $this->setFlashError($this->getWords()->getSilent('GroupNewAdminFailed'));
                $this->redirectAbsolute($this->router->url('groups_redirect'));
            }
            $this->redirectAbsolute($this->router->url('group_memberadministration',array('group_id' => $group->getPKValue())));
        }
    }


    /**
     * resigns a member as admin of a group
     *
     * @access public
     * @return object $page
     */
    public function resignAsAdmin()
    {
        $group = $this->_getGroupFromRequest();
        $resigner = $this->_model->getLoggedInMember();
        if (!$resigner) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } elseif (!$this->_model->canAccessGroupAdmin($group) && !$group->isGroupOwner($resigner)) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } else {
            $owners = $group->getGroupOwners();
            if (is_array($owners) && count($owners) < 2) {
                $this->setFlashError($this->getWords()->getSilent('GroupAdminResignationFailed_LastAdmin'));
            } else {
                $resigned = $this->_model->resignGroupAdmin($group, $resigner->getPKValue());
                if ($resigned)
                {
                    $this->logWrite("Member #{$resigner->Username} resigned as admin from the group #{$group->Name}");
                    $this->setFlashNotice($this->getWords()->getSilent('GroupAdminResignationSuccess'));
                } else {
                    $this->setFlashError($this->getWords()->getSilent('GroupAdminResignationFailed'));
                }
            }
            $page = new GroupStartPage;
            $this->_fillObject($page);
            $page->group = $group;
            return $page;
        }
    }


    /**
     * accepts a member to group
     *
     * @access public
     */
    public function acceptMember()
    {
        $group = $this->_getGroupFromRequest();
        $member_id = $this->_getMemberIdFromRequest();
        if (!$this->_model->getLoggedInMember()) {
            $this->redirectToLogin($this->router->url('group_acceptmember', array('group_id' => $group->getPKValue(), 'member_id' => $member_id), false));
        } elseif (!$this->_model->canAccessGroupAdmin($group) || empty($member_id)) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } else {
            $acceptedby = $this->_model->getLoggedInMember();
            $accepted = $this->_model->acceptGroupMember($group, $member_id, $acceptedby->getPKValue());
            if ($accepted) {
                $this->logWrite("Member #{$member_id} was accepted into group #{$group->getPKValue()} by member #{$this->_model->getLoggedInMember()->getPKValue()}");
                $this->setFlashNotice($this->getWords()->getSilent('GroupsMemberAcceptSuccess'));
            } else {
                $this->setFlashError($this->getWords()->getSilent('GroupsMemberAcceptFailed'));
                $this->redirectAbsolute($this->router->url('groups_redirect'));
            }
            $this->redirectAbsolute($this->router->url('group_memberadministration',array('group_id' => $group->getPKValue())));
        }
    }


    /**
     * handles member administration page
     *
     * @access public
     * @return object $page
     */
    public function memberAdministration()
    {
        $group = $this->_getGroupFromRequest();
        if (!$this->_model->getLoggedInMember()) {
            $this->redirectToLogin($this->router->url('group_memberadministration', array('group_id' => $group->getPKValue()), false));
        }
        elseif (!$this->_model->canAccessGroupAdmin($group)) {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
        } else {
            return $this->FillGroupMemberAdminPage($group);
        }
    }

    protected function FillGroupMemberAdminPage($group)
    {
        $isBWAdmin = false;
        $member = $this->_model->getLoggedInMember();
        $rights = $member->getOldRights();
        if ( !empty($rights) && in_array("Admin", array_keys($rights))) {
            $isBWAdmin = true;
        }

        $page = new GroupMemberAdministrationPage($group);
        $this->_fillObject($page);
        $page->$isBWAdmin = $isBWAdmin;
        $pager_params = new stdClass;
        $pager_params->strategy = new HalfPagePager;
        $pager_params->page_method = 'url';
        $pager_params->items = $page->group->getMemberCount();
        $pager_params->items_per_page = 50;
        $page->pager_widget = new PagerWidget($pager_params);
        return $page;
    }


    /**
     * handles member joining a group
     *
     * @access public
     * @return object $page
     */
    public function join()
    {
        $group = $this->_getGroupFromRequest();
        if (!($member = $this->_model->getLoggedInMember()))
        {
            $this->redirectToLogin($this->router->url('group_join', array('group_id' => $group->getPKValue()), false));
        }

        $page = new GroupJoinPage();
        $this->_fillObject($page);
        $page->group = $group;
        $page->member = $member;
        return $page;
    }

    /**
     * Callback function for joining a group
     *
     * @param object $args         contains vars
     * @param object $action       contains something else
     * @param object $mem_redirect memory for the page after redirect
     * @param object $mem_resend   memory for resending the form
     *
     * @return string relative request for redirect
     */
    public function joined($args, $action, $mem_redirect, $mem_resend)
    {
        $group = $this->_getGroupFromRequest();
        if (!($member = $this->_model->getLoggedInMember()))
        {
            return false;
        }

        $post = $args->post;
        if (empty($post) || empty($post['membershipinfo_acceptgroupmail']) || empty($post['join']))
        {
            $mem_redirect->post = $post;
            return false;
        }


        if ($this->_model->joinGroup($member, $group))
        {
            if ($group->Type == 'NeedAcceptance') {
                $this->setFlashNotice($this->getWords()->getSilent('GroupsJoinApprovalWait'));
            }
            $this->_model->updateMembershipSettings($member->id, $group->getPKValue(), $post['membershipinfo_acceptgroupmail'], !empty($post['membershipinfo_comment']) ? $post['membershipinfo_comment']:'');
            $this->logWrite("Member #{$this->_model->getLoggedInMember()->getPKValue()} joined group #{$group->getPKValue()}");
        }
        else
        {
            $mem_redirect->post = $post;
            $this->setFlashError($this->getWords()->getSilent('GroupsErrorJoiningGroup'));
            return $this->router->url('group_start', array('group_id' => $group->getPKValue()), false);;
        }
        return $this->router->url('group_start', array('group_id' => $group->getPKValue()), false);
    }

    /**
     * handles member leaving a group
     *
     * @access public
     * @return object $page
     */
    public function leave()
    {
        $group = $this->_getGroupFromRequest();
        if (!($member = $this->_model->getLoggedInMember()))
        {
            $this->redirectToLogin($this->router->url('group_leave', array('group_id' => $group->getPKValue()), false));
        }

        if ($group->isGroupOwner($member)) {
            $page = new GroupStartPage();
            $page->group = $group;
            $page->member = $this->_model->getLoggedInMember();
            $page->model = $this->_model;
            $this->setFlashError($this->getWords()->getSilent('GroupLeaveFail_ResignAdminFirst'));
        } else {
            $page = new GroupLeavePage();
            $page->member = $this->_model->getLoggedInMember();
            $page->group = $group;
        }
        return $page;
    }

    /**
     * handles member leaving a group
     *
     * @access public
     * @return object $page
     */
    public function left()
    {
        $group = $this->_getGroupFromRequest();
        if (!($member = $this->_model->getLoggedInMember()))
        {
            $this->redirectToLogin($this->router->url('group_leave', array('group_id' => $group->getPKValue()), false));
        }

        $page = new GroupStartPage();
        if ($this->_model->leaveGroup($member, $group))
        {
            $this->setFlashNotice($this->getWords()->getSilent('GroupsLeaveSuccess'));
            $this->logWrite("Member #{$this->_model->getLoggedInMember()->getPKValue()} left group #{$group->getPKValue()}");
        }
        else
        {
            $this->setFlashError($this->getWords()->getSilent('GroupsLeaveFail'));
        }
        $page->group = $group;
        $page->member = $member;
        return $page;
    }

    /**
     * handles member settings page
     *
     * @access public
     * @return object $page
     */
    public function memberSettings()
    {
        $group = $this->_getGroupFromRequest();
        if (!$this->_model->getLoggedInMember())
        {
            $this->redirectToLogin($this->router->url('group_membersettings', array('group_id' => $group->getPKValue()), false));
        }

        $page = ((isset($request[3]) && strtolower($request[3]) == 'true') ? new GroupStartPage() : new GroupMemberSettingsPage($group));
        $this->_fillObject($page);
        $page->group = $group;
        return $page;
    }

    /**
     * handles group settings page
     *
     * @access public
     * @return object $page
     */
    public function groupSettings()
    {
        $group = $this->_getGroupFromRequest();
        if (!$this->_model->canAccessGroupAdmin($group))
        {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
            PPHP::PExit();
        }

        $page = ((isset($request[3]) && strtolower($request[3]) == 'true') ? new GroupStartPage() : new GroupSettingsPage($group));
        $this->_fillObject($page);
        $page->group = $group;
        $page->group_members = $group->getMembers();
        return $page;
    }

    /**
     * handles group deletion page
     *
     * @access public
     * @return object $page
     */
    public function delete()
    {
        $group = $this->_getGroupFromRequest();
        if (!$this->_model->canAccessGroupAdmin($group))
        {
            $this->redirectAbsolute($this->router->url('groups_redirect'));
            PPHP::PExit();
        }

        $request = $this->request_vars;

        if (isset($request[3]) && strtolower($request[3]) == 'true')
        {
            $this->_model->deleteGroup($group);
            $this->logWrite("Group #{$group->getPKValue()} was deleted by member #{$this->_model->getLoggedInMember()->getPKValue()}");
            $this->redirectAbsolute($this->router->url('groups_redirect'));
            PPHP::PExit();
        }
        else
        {
            $page = new GroupDeletePage();
        }

        $this->_fillObject($page);
        $page->group = $group;
        return $page;
    }

    /**
     * handles showing group forum page
     *
     * @access public
     * @return object $page
     */
    public function forum()
    {
        $group = $this->_getGroupFromRequest();
        $page = new GroupForumPage($group);
        $this->_fillObject($page);
        if (!$page->member) {
            $this->setFlashNotice($this->_model->getWords()->get('GroupsFullFunctionalityLoggedIn'));
        }
        return $page;
    }

    public function groupForumsOverview()
    {
        $page = new GroupForumsOverviewPage();
        return $page;
    }

    /**
     * handles showing group members page
     *
     * @access public
     * @return object $page
     */
    public function members()
    {
        $group = $this->_getGroupFromRequest();
        $page = new GroupMembersPage($group);
        $this->_fillObject($page);
        $pager_params = new stdClass;
        $pager_params->strategy = new HalfPagePager;
        $pager_params->page_method = 'url';
        $pager_params->items = count($page->group->getMembers());
        $pager_params->items_per_page = 20;
        $page->pager_widget = new PagerWidget($pager_params);
        return $page;
    }

    /**
     * Callback function for createGroup page
     *
     * @param object $args         contains vars
     * @param object $action       contains something else
     * @param object $mem_redirect memory for the page after redirect
     * @param object $mem_resend   memory for resending the form
     *
     * @return string relative request for redirect
     */
    public function createGroupCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $count = $action->count;
        $redirect_req = $action->redirect_req;

        $mem_redirect->post = $args->post;

        $return = implode('/', $args->request);


        if (!$this->_model->getLoggedInMember())
        {
            // not logged in.
            // the login form will be shown after the automatic redirect
            // after successful login, the message is recovered.
            return $return;
        }

        if ($count < 0)
        {
            // session has expired while user was typing.
            $mem_redirect->expired = true;
            return $return;
        }

        if ($mem_resend->already_sent_as)
        {
            // form has already been processed, with the message sent!
            // for a new message, the user needs a new form.
            // tell the redirected page which message has been already sent!
            $mem_redirect->already_sent_as = $mem_resend->already_sent_as;
            return $return;
        }

        if ($count > 0)
        {
            // form has been already processed $count times,
            // but the last time it was not successful.
            // so, we can send again
            // but tell the page how many times it had failed before
            $mem_redirect->fail_count = $count;
        }

        //TODO: handle problems and creation of groups
        //TODO: figure out what the hell this function is supposed to do: group send or group creation??
        // now finally try to send it.
        $result = $this->_model->createGroup($args->post);
        if (count($result['problems']) > 0)
        {
            $mem_redirect->problems = $result['problems'];
            $mem_redirect->post = $args->post;
            return $return;
        }
        else
        {
            // sending message was successful
            // $mem_resend->already_sent_as = $result->message_id;
            // TODO: return message of success in creating a group
            $this->logWrite("Member #{$this->_model->getLoggedInMember()->getPKValue()} created group #{$result['group_id']}");
            return "group/" . $result['group_id'];
        }
    }

    /**
     * callback for changing member settings
     *
     * @param object $args         contains vars
     * @param object $action       contains something else
     * @param object $mem_redirect memory for the page after redirect
     * @param object $mem_resend   memory for resending the form
     *
     * @access public
     * @return string
     */
    public function changeMemberSettings($args, $action, $mem_redirect, $mem_resend)
    {
        $count = $action->count;

        $return = $args->req;

        if (!$this->_model->getLoggedInMember())
        {
            return $return;
        }

        if ($count < 0)
        {
            $mem_redirect->expired = true;
            return $return;
        }

        if ($mem_resend->already_sent_as)
        {
            $mem_redirect->already_sent_as = $mem_resend->already_sent_as;
            return $return;
        }

        $post = $args->post;
        if (empty($post['membershipinfo_acceptgroupmail']) || empty($post['group_id']) || empty($post['member_id']))
        {
            $mem_redirect->problems = true;
            return $return;
        }

        if ($this->_model->getLoggedInMember()->id == $post['member_id'])
        {
            $comment = ((!empty($post['membershipinfo_comment'])) ? $post['membershipinfo_comment'] : "");
            $result = $this->_model->updateMembershipSettings($post['member_id'], $post['group_id'], $post['membershipinfo_acceptgroupmail'], $comment);
        }
        else
        {
            // TODO: check for rights before updating ... but as these are not in place yet, let anyone do it
            $result = $this->_model->updateMembershipSettings($membership, $post['membershipinfo_acceptgroupmail'], $post['membershipinfo_comment'], '');
        }

        if ($result)
        {
            $this->logWrite("Member #{$this->_model->getLoggedInMember()->getPKValue()} changed own member settings for group #{$post['group_id']}");
        }
        $mem_redirect->result = $result;
        return $return;
    }

    /**
     * callback for changing group settings
     *
     * @param object $args         contains vars
     * @param object $action       contains something else
     * @param object $mem_redirect memory for the page after redirect
     * @param object $mem_resend   memory for resending the form
     *
     * @access public
     * @return string
     */
    public function changeGroupSettings($args, $action, $mem_redirect, $mem_resend)
    {
        $count = $action->count;

        $return = $args->req;

        if (!$this->_model->getLoggedInMember())
        {
            return $return;
        }

        if ($count < 0)
        {
            $mem_redirect->expired = true;
            return $return;
        }

        if ($mem_resend->already_sent_as)
        {
            $mem_redirect->already_sent_as = $mem_resend->already_sent_as;
            return $return;
        }

        $post = $args->post;
        if (empty($post['GroupDesc_']) || empty($post['Type']) || empty($post['group_id']) || empty($post['VisiblePosts']) || !($group = $this->_model->findGroup($post['group_id'])) || !$this->_model->canAccessGroupAdmin($group))
        {
            $mem_redirect->problems = array('General' => true);
            return $return;
        }

        $result = $this->_model->updateGroupSettings($group, $post['GroupDesc_'], $post['Type'], $post['VisiblePosts']);
        if ($result === true || !is_array($result)){
            $this->logWrite("Member #{$this->_model->getLoggedInMember()->getPKValue()} changed group settings for group #{$post['group_id']}");
            $mem_redirect->result = true;
        } else {
            $mem_redirect->problems = $result;
            $mem_redirect->result = false;
        }
        $mem_redirect->post = $post;
        return $return;
    }

    public function newPostCallback($args, $action, $mem_redirect, $mem_resend)
    {
        $count = $action->count;

        $return = $args->req;

        if (!$this->_model->getLoggedInMember())
        {
            return $return;
        }

        if ($count < 0)
        {
            $mem_redirect->expired = true;
            return $return;
        }

        if ($mem_resend->already_sent_as)
        {
            $mem_redirect->already_sent_as = $mem_resend->already_sent_as;
            return $return;
        }

        $vars = $args->post;
        $forumsModel = new Forums();
        $vars_ok = $forumsModel->checkVarsTopic($vars);
        if ($vars_ok) {
            $threadId = $forumsModel->newTopic($vars);
            $mem_redirect->result = true;
            return "group/" . $vars['IdGroup'] . '/forum/s' . $threadId;
        }
        $mem_redirect->vars = $vars;

        return $return;
    }

    public function newPost()
    {
        $group = $this->_getGroupFromRequest();
        $page = new GroupNewPostPage($group);
        $page->vars = $this->args_vars;
        $this->_fillObject($page);
        return $page;
    }

}

