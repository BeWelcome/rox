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
 * AdminNewMembers controller
 * Handles the NewMemberBeWelcome actions. Offers SafetyTeam a quick way of detecting spam profiles.
 *
 * @package apps
 * @subpackage Admin
 */
class AdminNewMembersController extends AdminBaseController
{
    const MEMBERS_PER_PAGE = 30;

    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new AdminNewMembersModel();
    }

    public function __destruct() {
        unset($this->model);
    }

    public function setStatusCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
        $vars = $this->args_vars->post;
        $member = new Member($vars['member-id']);
        $member->Status = $vars['new-status'];
        $member->update();
        return true;
    }

    public function listMembersCallback(StdClass $args, ReadOnlyObject $action,
        ReadWriteObject $mem_redirect, ReadWriteObject $mem_resend)
    {
    }

    public function listMembers()
    {
        list($loggedInMember, $rights) = $this->CheckRights();
        $safetyTeamOrAdmin = false;
        if (isset($rights['SafetyTeam']) || isset($Rights['Admin'])) {
            $safetyTeamOrAdmin = true;
        }
        $newMemberBeWelcomeTeam = false;
        if (isset($rights['NewMembersBeWelcome'])) {
            $newMemberBeWelcomeTeam = true;
        }
        if (!($safetyTeamOrAdmin | $newMemberBeWelcomeTeam)) {
            $this->redirectAbsolute('/');
        }
        $pageno = 1;
        if (isset($this->route_vars['pageno'])) {
            $pageno = ($this->route_vars['pageno']);
        }
        $start = ($pageno - 1) * self::MEMBERS_PER_PAGE;

        $page = new AdminNewMembersListMembersPage();
        $page->current = 'AdminNewMembersListMembers';
        $page->count = $this->model->getMembersCount($safetyTeamOrAdmin);
        $page->members = $this->model->getMembers($start, self::MEMBERS_PER_PAGE, $safetyTeamOrAdmin);

        $params = new StdClass;
        $params->strategy = new FullPagePager();
        $params->page_url = 'admin/newmembers/';
        $params->page_url_marker = 'page';
        $params->page_method = 'url';
        $params->items = $page->count;
        $params->active_page = $pageno;
        $params->items_per_page = self::MEMBERS_PER_PAGE;
        $pager = new PagerWidget($params);
        $page->pager = $pager;
        if ($safetyTeamOrAdmin) {
            $page->SafetyTeamOrAdmin = true;
            $page->url = implode('/', $this->request_vars);
        }
        return $page;
    }

    public function composeMessage()
    {
        $username = $this->route_vars['username'];
        $request = $this->request_vars[2];
        $memberEntity = new Member();
        $member = $memberEntity->findByUsername($username);
        if ($member) {
            switch($request) {
                case 'local':
                    $this->model->localGreetingSent($member);
                    break;
                case 'global':
                    $this->model->globalGreetingSent($member);
                    break;
            }
            $this->redirectAbsolute('/messages/compose/' . $username);
        } else {
            $this->redirectAbsolute('/members/' . $username);
        }
    }
}