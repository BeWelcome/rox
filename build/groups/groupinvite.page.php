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
     * This page allows to create a new group
     *
     * @package Apps
     * @subpackage Groups
     */
class GroupInvitePage extends GroupsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        $words = $this->getWords();
        ?>
        <div id="teaser" class="page-teaser clearfix">
        <div id="teaser_l1"> 
        <h1><a href="groups"><?= $words->get('Groups');?></a> &raquo; <a href=""><?= $words->get('GroupsInviteMembers');?></a></h1>
        </div>
        </div>
        <?php
    }

    protected function getSubmenuActiveItem()
    {
        return 'members';
    }

    protected function column_col3()
    {
        // get translation module
        $layoutkit = $this->layoutkit;
        $words = $layoutkit->getWords();
        $model = $this->getModel();

        echo <<<HTML
    <div id="groups">
        <h3>{$words->get('GroupsInviteMembers')}</h3>
HTML;
        if ($search = $this->search_result)
        {
            echo <<<HTML
            <ul>
HTML;
            foreach ($search as $member)
            {
                echo <<<HTML
                <li><a href='groups/{$this->group->getPKValue()}/invitemember/{$member->getPKValue()}' title='{$words->get('GroupsClickToSendInvite')}'>{$words->get('GroupsInviteMember',$member->Username)}</li>
HTML;
            }
        }
        else
        {
            echo $words->get('GroupSearchNoResults');
        }
    echo "  </div>";
    }
}


