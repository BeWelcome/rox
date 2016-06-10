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
     * This page asks if the user wants to leave the group
     *
     * @package Apps
     * @subpackage Groups
     */
class GroupLeavePage extends GroupsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        $a = new APP_User();
        if (!$a->isBWLoggedIn('NeedMore,Pending'))
        {
            $widg = $this->createWidget('LoginFormWidget');
            $widg->render();
        }
        else
        {
        ?>
        <h3><?= $words->get('GroupsLeaveNamedGroup', htmlspecialchars($this->getGroupTitle(), ENT_QUOTES)); ?></h3>
        <a class="button" role="button" href="groups/<?=$this->group->id ?>/leave/true"><?= $words->get('GroupsYesGetMeOut');?></a>
        <a href="groups/<?=$this->group->id ?>"><?= $words->get('GroupsNoIStay');?></a>
        <?php
        }
    }
    
    protected function getSubmenuActiveItem() {
        return 'leave';
    }
}


?>
