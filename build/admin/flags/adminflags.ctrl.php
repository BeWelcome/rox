<?php
/*

Copyflag (c) 2007-2009 BeVolunteer

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
 * adminFlags controller
 * deals with actions that are available exclusively for Flags managers
 *
 * @package apps
 * @subpackage Admin
 */
class AdminFlagsController extends AdminBaseController
{
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new AdminFlagsModel();
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
        $this->model->assignFlag($vars);
        $Flags = $this->model->getFlags();
        $flag = $Flags[$vars['flagid']];
        $this->setFlashNotice($this->getWords()->get('AdminFlagsFlagAssigned', $vars['username'], $flag->Name));

        return $this->router->url('admin_flags_member', array("username" => $vars['username']), false);
    }

    public function assign() {
        $this->checkRights('Flags');
        $member = false;
        if (isset($this->route_vars['username'])) {
            $temp = new Member();
            $member = $temp->findByUsername($this->route_vars['username']);
        };
        $page = new AdminFlagsAssignPage();
        $page->member = $member;
        $page->vars = array(
            'username' => ($member ? $member->Username : ''),
            'flagid' => 0,
            'level' => 0,
            'scope' => '',
            'comment' => '');
        $page->flags = $this->model->getFlags(true, $member);
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
        $mem_redirect->members = $this->model->getMembersWithFlags(false, $history);
        // get list of members with Flags (as assigned, filter by $member if set)
        $mem_redirect->membersWithFlags = $this->model->getMembersWithFlags($member, $history);
    }

    public function listMembers()
    {
        $this->checkRights('Flags');
        $member = false;
        if (isset($this->route_vars['username'])) {
            $temp = new Member();
            $member = $temp->findByUsername($this->route_vars['username']);
        };
        $page = new AdminFlagsListMembersPage();
        $page->vars = array(
            'member' => ($member ? $member->id : 0)
        );
        $page->current = 'AdminFlagsListMembers';
        $page->flags = $this->model->getFlags();
        // get list of members (with assigned Flags)
        $page->members = $this->model->getMembersWithFlags();
        // get list of members with Flags (as assigned, filter by $member if set)
        $page->membersWithFlags = $this->model->getMembersWithFlags($member);
        if (($member) && (count($page->membersWithFlags) == 0)) {
            $this->redirectAbsolute('/admin/flags/assign/' . $this->route_vars['username']);
        }
        return $page;
    }

    public function listFlagsCallback(StdClass $args, ReadOnlyObject $action,
                                      ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $args->post;
        $flagId = false;
        if (isset($vars['flagid']) && $vars['flagid'] <> '0') {
            $flagId = $vars['flagid'];
        }
        $history = false;
        if (isset($vars['history']) && $vars['history'] <> '0') {
            $history = true;
        }
        $mem_redirect->vars = $vars;
        $mem_redirect->FlagsWithMembers = $this->model->getFlagsWithMembers($flagId, $history);
        return true;
    }

    public function listFlags()
    {
        $this->checkRights('Flags');
        $flagId = false;
        if (isset($this->route_vars['id']) && is_numeric($this->route_vars['id'])) {
            $flagId = $this->route_vars['id'];
        };
        $page = new AdminFlagsListFlagsPage();
        $page->flags = $this->model->getFlags();
        $page->vars = array(
            'flagid' => $flagId
        );
        $page->flagsWithMembers = $this->model->getFlagsWithMembers($flagId);
        return $page;
    }

    public function overview() {
        $this->checkRights('Flags');
        $page = new AdminFlagsOverviewPage();
        $page->current = 'AdminFlagsOverview';
        $page->flags = $this->model->getFlags();
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
            $vars['flag'] = $vars['flagid'];

            $mem_redirect->vars = $vars;
            return false;
        }
        $this->model->edit($vars);
        $this->setFlashNotice($this->getWords()->get('AdminFlagsFlagEdited'));
        return true;
    }

    public function edit()
    {
        $this->checkRights('Flags');

        $flagId = $this->route_vars['id'];
        $username = $this->route_vars['username'];
        // Check if flag and user exist and if flag is assigned to user at all; redirect if not
        $flag = new Flag($flagId);
        if (!$flag) {
            $this->redirectAbsolute($this->router->url('admin_flags_overview'));
        }
        $temp = new Member();
        $member = $temp->findByUsername($username);
        if (!$member) {
            $this->redirectAbsolute($this->router->url('admin_flags_overview'));
        }
        $assigned = $flag->getFlagForMember($member);
        if (!$assigned) {
            $this->redirectAbsolute($this->router->url('admin_flags_overview'));
        }

        $page = new AdminFlagsEditPage();
        $page->flags = $this->model->getFlags(true);
        $vars = array(
            'username' => $username,
            'flag' => $flagId,
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
        $flags = $this->model->getFlags();
        $flag = $flags[$vars['flagid']];
        $this->setFlashNotice($this->getWords()->get('AdminFlagsFlagRemoved', $vars['username'], $flag->Name ));
        switch ($vars['redirect']) {
            case 'members':
                $url = $this->router->url('admin_flags_members', array(), false);
                break;
            case 'member':
                $url = $this->router->url('admin_flags_member', array("username" => $vars['username']), false);
                break;
            case 'Flags':
                $url = $this->router->url('admin_flags_Flags', array(), false);
                break;
            case 'flag':
                $url = $this->router->url('admin_flags_flag', array("id" => $vars['flag']), false);
                break;
            default:
                $url = $this->router->url('admin_Flags', array(), false);
        }
        $this->model->remove($vars);
        return $url;
    }

    public function remove()
    {
        $this->checkRights('Flags');
        $flagId = $this->route_vars['id'];
        $username = $this->route_vars['username'];
        // Check if flag and user exist and if flag is assigned to user at all; redirect if not
        $flag = new Flag($flagId);
        if (!$flag) {
            $this->redirectAbsolute($this->router->url('admin_flags_overview'));
        }
        $temp = new Member();
        $member = $temp->findByUsername($username);
        if (!$member) {
            $this->redirectAbsolute($this->router->url('admin_flags_overview'));
        }
        $assigned = $flag->getFlagForMember($member);
        if (!$assigned) {
            $this->redirectAbsolute($this->router->url('admin_flags_overview'));
        }
        $page = new AdminFlagsRemovePage();

        $flags = $this->model->getFlags(true);
        $page->flags = $flags;
        $redirectTo = '';
        if (isset($_SERVER['HTTP_REFERER'])) {
            if (strpos($_SERVER['HTTP_REFERER'], "/list/members") !== false) {
                $redirectTo = 'members';
            }
            if (strpos($_SERVER['HTTP_REFERER'], "/list/member/") !== false) {
                $redirectTo = 'member';
            }
            if (strpos($_SERVER['HTTP_REFERER'], "/list/Flags") !== false) {
                $redirectTo = 'Flags';
            }
            if (strpos($_SERVER['HTTP_REFERER'], "/list/flag/") !== false) {
                $redirectTo = 'flag';
            }
        }
        $vars = array(
            'username' => $username,
            'flag' => $flagId,
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
        $this->model->createFlag($vars);
        $this->setFlashNotice($this->getWords()->get('AdminFlagsFlagCreate', $vars['name']));
        return $this->router->url('admin_flags_overview', array(), false);
    }

    public function create()
    {
        list($loggedInMember, $Rights) = $this->checkRights('Flags');
        // Check if member has create flag if not redirect to overview
        if ((stripos($Rights['Flags']['Scope'], 'create') === false
            && stripos($Rights['Flags']['Scope'], 'all') === false)) {
            $this->redirectAbsolute($this->router->url('admin_flags_overview'));
        }
        $page = new AdminFlagsCreatePage();
        $vars = array(
            'name' => '',
            'description' => ''
        );
        $page->vars = $vars;
        return $page;
    }
}