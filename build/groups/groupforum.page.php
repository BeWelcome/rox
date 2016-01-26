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
     * handles the forum page for groups
     *
     * @package Apps
     * @subpackage Groups
     */

class GroupForumPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        if (!$this->member) {
            $loginWidget = $this->layoutkit->createWidget('LoginFormWidget');
            $loginWidget->render();
        } else {

            if (!$this->isGroupMember() && $this->group->Type == 'NeedInvitation') {
                echo $words->get('GroupsNotPublic');
            } else {
                $group_id = $this->group->id;

                $memberlist_widget = new GroupMemberlistWidget();
                $memberlist_widget->setGroup($this->group);

                $Forums = new ForumsController;
                $Forums->index('groups');
                //$forums_widget->setGroup($this->getGroup());

                //include "templates/groupforum.column_col3.php";
            }
        }
    }

    protected function getSubmenuActiveItem() {
        return 'forum';
    }
    
}


class GroupForumsOverviewPage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();

    ?>
        <div>
            <h3><?= $words->get('GroupsSearchHeading'); ?></h3>
            <form action="groups/search" method="get">
                <input type="text" name="GroupsSearchInput" value="" id="GroupsSearchInput" /><input type="submit" class="button" value="<?= $words->getSilent('GroupsSearchSubmit'); ?>" /><?=$words->flushBuffer()?><br />
            </form>
        </div>             
    <?php 
        $Forums = new ForumsController;
        $Forums->index();
    }
    protected function teaserContent()
    {
        $words = $this->getWords();
        ?>
        <div id="teaser" class="page-teaser clearfix">
        <div id="teaser_l1"> 
        <h1><a href="forums"><?= $words->get('CommunityLanding');?></a> &raquo <a href="groups/forums"><?= $words->get('Groups');?></a></h1>
        </div>
        </div>
        <?php
    }    
}

?>
