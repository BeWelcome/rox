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

    /**
     * This page allows for searching for groups
     *
     * @package Apps
     * @subpackage Groups
     */

class GroupsSearchPage extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div>
            <h2><a href="groups/search"><?= $words->get('Groups');?></a> &raquo;<?= $words->get('search');?></h2>
        </div>
        <?php
    }

    protected function getSubmenuItems()
    {
        $words = $this->getWords();
        $items = array();
        $items[] = array('mygroups', 'groups/mygroups', $words->getSilent('GroupsMyGroups'));
        $items[] = array('search', 'groups/search', $words->getSilent('GroupsFindGroups'));

        $isForumModerator = $this->member->hasOldRight(['ForumModerator' => 10]);

        if ($isForumModerator)
        {
            $forumsModel = new Forums();
            $items[] = ['separator'];
            $items[] = ['allmyreports', 'forums/reporttomod/AllMyReport', 'All reports for me'];
            $items[] = [
                'myactivereports',
                'forums/reporttomod/MyReportActive',
                'Pending reports for me <span class="badge badge-default">'
                . $forumsModel->countReportList($this->session->get("IdMember"),
                    "('Open','OnDiscussion')"
                )
                . '</span>'
            ];
            $items[] = [
                'allactivereports',
                'forums/reporttomod/AllActiveReports',
                'All pending reports <span class="badge badge-default">'
                . $forumsModel->countReportList(0,"('Open','OnDiscussion')")
                . '</span>'
            ];
            $items[] = ['separator'];
            $items[] = [
                'groupadmin',
                '/admin/groups/approval',
                'Group Administration'
            ];
            $items[] = [
                'grouplogs',
                'admin/logs/groups',
                'Group Logs'
            ];
        }

        return $items;
    }

    protected function getSubmenuActiveItem()
    {
        return 'search';
    }


}
