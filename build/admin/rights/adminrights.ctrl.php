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
 * @author shevek
 */

/**
 * adminrights controller
 * deals with actions that are available exclusively for rights managers
 *
 * @package apps
 * @subpackage Admin
 */
class AdminRightsController extends AdminBaseController
{
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new AdminRightsModel();
    }

    public function __destruct() {
        unset($this->model);
    }

    public function assignCallback(StdClass $args, ReadOnlyObject $action,
                                        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $errors = $this->model->checkAssignVarsOk($vars);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        }
        $this->model->assignRight($vars);
        return true;
    }

    public function assign() {
        $this->checkRights('Rights');
        $member = false;
        if (isset($this->route_vars['username'])) {
            $temp = new Member();
            $member = $temp->findByUsername($this->route_vars['username']);
        };
        $page = new AdminRightsAssignPage();
        $page->member = $member;
        $page->vars = array(
            'username' => ($member ? $member->Username : ''),
            'right' => 0,
            'level' => 0,
            'scope' => '',
			'comment' => '');
        $page->rights = $this->model->getRights(true, $member);
        return $page;
    }

    public function listMembersCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $member = false;
        if (isset($vars['member']) && $vars['member'] <> '0') {
            $temp = new Member();
            $member = $temp->findById($vars['member']);
        }
        $history = false;
        if (isset($vars['history']) && $vars['history'] <> '0') {
            $history= true;
        }
        $mem_redirect->vars = $vars;
        $mem_redirect->members = $this->model->getMembersWithRights(false, $history);
        // get list of members with rights (as assigned, filter by $member if set)
        $mem_redirect->membersWithRights = $this->model->getMembersWithRights($member, $history);
    }

    public function listMembers()
    {
        $this->checkRights('Rights');
        $member = false;
        if (isset($this->route_vars['username'])) {
            $temp = new Member();
            $member = $temp->findByUsername($this->route_vars['username']);
        };
        $page = new AdminRightsListMembersPage();
        $page->vars = array(
            'member' => $member,
            'history' => 0
        );
        $page->current = 'AdminRightsListMembers';
        $page->rights = $this->model->getRights();
        // get list of members (with assigned rights)
        $page->members = $this->model->getMembersWithRights();
        // get list of members with rights (as assigned, filter by $member if set)
        $page->membersWithRights = $this->model->getMembersWithRights($member);
        return $page;
    }

    public function listRightsCallback(StdClass $args, ReadOnlyObject $action,
                                      ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $rightId = false;
        if (isset($vars['right']) && $vars['right'] <> '0') {
            $rightId = $vars['right'];
        }
        $history = false;
        if (isset($vars['history']) && $vars['history'] <> '0') {
            $history = true;
        }
        $mem_redirect->vars = $vars;
        $mem_redirect->rightsWithMembers = $this->model->getRightsWithMembers($rightId, $history);
        return true;
    }

    public function listRights()
    {
        $this->checkRights('Rights');
        $rightId = false;
        if (isset($this->route_vars['id']) && is_numeric($this->route_vars['id'])) {
            $rightId = $this->route_vars['id'];
        };
        $page = new AdminRightsListRightsPage();
        $page->rights = $this->model->getRights();
        $page->vars = array(
            'rightid' => $rightId,
            'history' => 0
        );
        $page->rightsWithMembers = $this->model->getRightsWithMembers($rightId);
        return $page;
    }

    public function overview() {
        $this->checkRights('Rights');
        $page = new AdminRightsOverviewPage();
        $page->current = 'AdminRightsOverview';
        $page->rights = $this->model->getRights();
        return $page;
    }

    public function tooltip() {
        $id = $this->args_vars->get['tooltip'];
        header('Content-type: text/html, charset=utf-8');
         $javascript = $this->model->getWords()->get($id);
        echo $javascript . "\n";
        exit;
    }

    public function editCallback(StdClass $args, ReadOnlyObject $action,
                                       ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $errors = $this->model->checkEditVarsOk($vars);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;

            // Set variables (necessary due to disabled flag)
            $vars['right'] = $vars['rightid'];

            $mem_redirect->vars = $vars;
            return false;
        }
        $this->model->edit($vars);
        $this->setFlashNotice($this->getWords()->get('AdminRightsRightEdited'));
        return true;
    }

    public function edit()
    {
        $this->checkRights('Rights');

        $rightId = $this->route_vars['id'];
        $username = $this->route_vars['username'];
        // Check if right and user exist and if right is assigned to user at all; redirect if not
        $right = new Right($rightId);
        if (!$right) {
            $this->redirectAbsolute($this->router->url('admin_rights_overview'));
        }
        $temp = new Member();
        $member = $temp->findByUsername($username);
        if (!$member) {
            $this->redirectAbsolute($this->router->url('admin_rights_overview'));
        }
        $assigned = $right->getRightForMember($member);
        if (!$assigned) {
            $this->redirectAbsolute($this->router->url('admin_rights_overview'));
        }

        $page = new AdminRightsEditPage();
        $page->rights = $this->model->getRights(true);
        $vars = array(
            'username' => $username,
            'right' => $rightId,
            'level' => $assigned->Level,
            'scope' => $assigned->Scope,
            'comment' => $assigned->Comment
        );
        $page->vars = $vars;
        return $page;
    }

    public function removeCallback(StdClass $args, ReadOnlyObject $action,
                                 ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $rights = $this->model->getRights();
        $right = $rights[$vars['rightid']];
        $this->setFlashNotice($this->getWords()->get('AdminRightsRightRemoved', $vars['username'], $right->Name ));
        switch ($vars['redirect']) {
            case 'members':
                $url = $this->router->url('admin_rights_members', array(), false);
                break;
            case 'member':
                $url = $this->router->url('admin_rights_member', array("username" => $vars['username']), false);
                break;
            case 'rights':
                $url = $this->router->url('admin_rights_rights', array(), false);
                break;
            case 'right':
                $url = $this->router->url('admin_rights_right', array("id" => $vars['right']), false);
                break;
            default:
                $url = $this->router->url('admin_rights', array(), false);
        }
        $this->model->remove($vars);
        return $url;
    }

    public function remove()
    {
        $this->checkRights('Rights');
        $rightId = $this->route_vars['id'];
        $username = $this->route_vars['username'];
        // Check if right and user exist and if right is assigned to user at all; redirect if not
        $right = new Right($rightId);
        if (!$right) {
            $this->redirectAbsolute($this->router->url('admin_rights_overview'));
        }
        $temp = new Member();
        $member = $temp->findByUsername($username);
        if (!$member) {
            $this->redirectAbsolute($this->router->url('admin_rights_overview'));
        }
        $assigned = $right->getRightForMember($member);
        if (!$assigned) {
            $this->redirectAbsolute($this->router->url('admin_rights_overview'));
        }
        $page = new AdminRightsRemovePage();

        $rights = $this->model->getRights(true);
        $page->rights = $rights;
        $redirectTo = '';
        if (isset($_SERVER['HTTP_REFERER'])) {
            if (strpos($_SERVER['HTTP_REFERER'], "/list/members") !== false) {
                $redirectTo = 'members';
            }
            if (strpos($_SERVER['HTTP_REFERER'], "/list/member/") !== false) {
                $redirectTo = 'member';
            }
            if (strpos($_SERVER['HTTP_REFERER'], "/list/rights") !== false) {
                $redirectTo = 'rights';
            }
            if (strpos($_SERVER['HTTP_REFERER'], "/list/right/") !== false) {
                $redirectTo = 'right';
            }
        }
        $vars = array(
            'username' => $username,
            'right' => $rightId,
            'level' => $assigned->Level,
            'scope' => $assigned->Scope,
            'comment' => $assigned->Comment,
            'redirect' => $redirectTo
        );
        $page->vars = $vars;
        return $page;
    }

    public function createCallback(StdClass $args, ReadOnlyObject $action,
                                 ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $errors = $this->model->checkCreateVarsOk($vars);
        if (count($errors) > 0) {
            $mem_redirect->errors = $errors;
            $mem_redirect->vars = $args->post;
            return false;
        }
        $this->model->createRight($vars);
        $this->setFlashNotice($this->getWords()->get('AdminRightsRightCreate', $vars['name']));
        return $this->router->url('admin_rights_overview', array(), false);
    }

    public function create()
    {
        list($loggedInMember, $rights) = $this->checkRights('Rights');
        // Check if member has create right if not redirect to overview
        if ((stripos($rights['Rights']['Scope'], 'create') === false
            && stripos($rights['Rights']['Scope'], 'all') === false)) {
            $this->redirectAbsolute($this->router->url('admin_rights_overview'));
        }
        $page = new AdminRightsCreatePage();
        $vars = array(
            'name' => '',
            'description' => ''
        );
        $page->vars = $vars;
        return $page;
    }
}