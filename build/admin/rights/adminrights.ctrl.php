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

    #[\Override]
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
        $rights = $this->model->getRights();
        $right = $rights[$vars['rightid']];
        $this->setFlashNotice($this->getWords()->get('AdminRightsRightAssigned', $vars['username'], $right->Name));

        return $this->router->url('admin_rights_member', ["username" => $vars['username']], false);
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
        $page->vars = [
            'username' => ($member ? $member->Username : ''),
            'rightid' => 0,
            'level' => 0,
            'scope' => '',
            'comment' => ''];
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
        $mem_redirect->members = $this->model->getMembersWithRights(false);
        // get list of members with rights (as assigned, filter by $member if set)
        $mem_redirect->membersWithRights = $this->model->getMembersWithRights($member);
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
        $page->vars = [
            'member' => ($member ? $member->id : 0)
        ];
        $page->current = 'AdminRightsListMembers';
        $page->rights = $this->model->getRights();
        // get list of members (with assigned rights)
        $page->members = $this->model->getMembersWithRights();
        // get list of members with rights (as assigned, filter by $member if set)
        $page->membersWithRights = $this->model->getMembersWithRights($member);
        if (($member) && (count($page->membersWithRights) == 0)) {
            $this->redirectAbsolute('/admin/rights/assign/' . $this->route_vars['username']);
        }
        return $page;
    }

    public function listRightsCallback(StdClass $args, ReadOnlyObject $action,
                                      ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $rightId = false;
        if (isset($vars['rightid']) && $vars['rightid'] <> '0') {
            $rightId = $vars['rightid'];
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
        $page->vars = [
            'rightid' => $rightId
        ];
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

    public function tooltip(): never {
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
        $vars = [
            'username' => $username,
            'right' => $rightId,
            'level' => $assigned->Level,
            'scope' => $assigned->Scope,
            'comment' => $assigned->Comment
        ];
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
        $url = match ($vars['redirect']) {
            'members' => $this->router->url('admin_rights_members', [], false),
            'member' => $this->router->url('admin_rights_member', ["username" => $vars['username']], false),
            'rights' => $this->router->url('admin_rights_rights', [], false),
            'right' => $this->router->url('admin_rights_right', ["id" => $vars['right']], false),
            default => $this->router->url('admin_rights', [], false),
        };
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
            if (str_contains((string) $_SERVER['HTTP_REFERER'], "/list/members")) {
                $redirectTo = 'members';
            }
            if (str_contains((string) $_SERVER['HTTP_REFERER'], "/list/member/")) {
                $redirectTo = 'member';
            }
            if (str_contains((string) $_SERVER['HTTP_REFERER'], "/list/rights")) {
                $redirectTo = 'rights';
            }
            if (str_contains((string) $_SERVER['HTTP_REFERER'], "/list/right/")) {
                $redirectTo = 'right';
            }
        }
        $vars = [
            'username' => $username,
            'right' => $rightId,
            'level' => $assigned->Level,
            'scope' => $assigned->Scope,
            'comment' => $assigned->Comment,
            'redirect' => $redirectTo
        ];
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
        return $this->router->url('admin_rights_overview', [], false);
    }

    public function create()
    {
        [$loggedInMember, $rights] = $this->checkRights('Rights');
        // Check if member has create right if not redirect to overview
        if ((stripos((string) $rights['Rights']['Scope'], 'create') === false
            && stripos((string) $rights['Rights']['Scope'], 'all') === false)) {
            $this->redirectAbsolute($this->router->url('admin_rights_overview'));
        }
        $page = new AdminRightsCreatePage();
        $vars = [
            'name' => '',
            'description' => ''
        ];
        $page->vars = $vars;
        return $page;
    }
}