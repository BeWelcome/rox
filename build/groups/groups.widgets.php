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

class GroupForumWidget extends ForumPreviewWidget
{

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
    private $_limit;
    
    public function render()
    {
        $memberships = $this->_group->getMembers();
        $membercount = count($memberships);
        $limit = ($this->_limit ? $this->_limit : 6);
        for ($i = 0; $i < $membercount && $i < $limit; $i++)
        {
            $idx = $membercount - $i - 1;
            echo <<<HTML
            <div class="groupmembers center float_left">                
HTML;
                echo MOD_layoutbits::PIC_50_50($memberships[$idx]->Username);
                echo <<<HTML
                <a href="members/{$memberships[$idx]->Username}">{$memberships[$idx]->Username}</a>               
            </div>
HTML;
        }
    }
    
    public function setGroup($group)
    {
        // extract memberlist information from the $group object
        $this->_group = $group;
    }
    
    public function setLimit($limit)
    {
        // set a maximum of members to be shown, defaults 6
        $this->_limit = $limit;
    }
}

