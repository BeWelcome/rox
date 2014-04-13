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
        $page = new AdminRightsAssignPage();
        $this->vars = array(
            'username' => '',
            'right' => 0,
            'level' => 0,
            'scope' => '');
        $page->rights = $this->model->getRights();
        return $page;
    }

    public function listMembersCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        if (isset($vars['member']) && $vars['member'] <> '0') {
            return $this->router->url('admin_rights_member', array("username" => $vars['member']), false);
        } else {
            return $this->router->url('admin_rights_members', array(), false);
        }
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
        $page->current = 'AdminRightsListMembers';
        $page->rights = $this->model->getRights();
        $page->member = $member;
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
        if (isset($vars['right']) && $vars['right'] <> '0') {
            return $this->router->url('admin_rights_right', array("id" => $vars['right']), false);
        } else {
            return $this->router->url('admin_rights_rights', array(), false);
        }
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
        $page->rightId = $rightId;
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
            $mem_redirect->vars = $args->post;
            return false;
        }
        $this->model->editRight($vars);
        return true;
    }

    public function edit()
    {
        $this->checkRights('Rights');
        $rightId = $this->route_vars['id'];
        $username = $this->route_vars['username'];
        // Check if rights exist, redirect if not
        $page = new AdminRightsEditPage();
        $page->rights = $this->model->getRights();
        $temp = new Member();
        $member = $temp->findByUsername($username);
        $page->member = $member;
        list($level, $scope) = $this->model->getRightScopeAndLevelForMember($member, $rightId);
        $vars = array(
            'username' => $username,
            'right' => $rightId,
            'level' => $level,
            'scope' => $scope
        );
        $page->vars = $vars;
        return $page;
    }

    public function removeCallback(StdClass $args, ReadOnlyObject $action,
                                 ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        // $this->model->removeRight($vars);
        $this->setFlashNotice($this->getWords()->get('AdminRightsRightRemoved', $vars['username'], $vars['right']));
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
        return $url;
    }

    public function remove()
    {
        $this->checkRights('Rights');
        $rightId = $this->route_vars['id'];
        $username = $this->route_vars['username'];
        // Check if rights exist, redirect if not
        $page = new AdminRightsRemovePage();

        $rights = $this->model->getRights();
        $page->rights = $rights;
        $temp = new Member();
        $member = $temp->findByUsername($username);
        $page->member = $member;
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
        list($level, $scope) = $this->model->getRightScopeAndLevelForMember($member, $rightId);
        $vars = array(
            'username' => $username,
            'right' => $rightId,
            'level' => $level,
            'scope' => $scope,
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
        return true;
    }

    public function create()
    {
        list($loggedInMember, $rights) = $this->checkRights('Rights');
        error_log(print_r($rights, true));
        // Check if member has create right if not redirect to overview
        if (!(($rights['Rights']['Level'] == 10) && (stripos($rights['Rights']['Scope'], 'create') !== false
            || stripos($rights['Rights']['Scope'], 'all') !== false))) {
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