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
     * @author Lemon-Head
     * @author Micha
     * @author Globetrotter_tt
     */

    /**
     * This widget shows the forum for a group page
     *
     * @package Apps
     * @subpackage Widgets
     */

class GroupForumWidget  // extends ForumBoardWidget
{
    public function render()
    {
        echo 'group forum';
    }

    public function setGroup($group)
    {
        // extract information from the $group object
    }
}

//------------------------------------------------------------------------------------
    /**
     * This widget shows a list of members with pictures.
     *
     * @package Apps
     * @subpackage Widgets
     */
class GroupMemberlistWidget  // extends MemberlistWidget?
{
    private $_group;

    public function render()
    {
        $numberOfMembers = 24;
        $memberships = $this->_group->getLastLoggedInMembers($numberOfMembers);
        foreach ($memberships as $membership)
        { ?>
            <div class="pb-1">
            <?= MOD_layoutbits::PIC_50_50($membership->Username); ?>
            </div>
        <?
        }
    }

    public function setGroup($group)
    {
        // extract memberlist information from the $group object
        $this->_group = $group;
    }
}

