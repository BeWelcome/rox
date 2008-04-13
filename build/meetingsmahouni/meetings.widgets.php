<?php
/*
Copyright (c) 2007 BeVolunteer

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
 * meetings widgets
 *
 * @package meetings
 * @author BeVolunteer - Toni (mahouni) (based on lemon-head`s groups application)
 */



//------------------------------------------------------------------------------------
/**
 * This widget shows the forum for a meeting page
 *
 */
class MeetingForumWidget  // extends ForumBoardWidget
{
    public function render()
    {
        echo 'meeting forum';
    }
    
    public function setMeeting($meeting)
    {
        // extract information from the $meeting object
    }
}

//------------------------------------------------------------------------------------
/**
 * This widget shows a list of members with pictures.
 */
class MeetingMemberlistWidget  // extends MemberlistWidget?
{
    private $_meeting;
    
    public function render()
    {
        $memberships = $this->_meeting->getMemberships(10);
        foreach ($memberships as $membership) {
            ?><div style="float:left; border:1px solid #fec;">
                <?=MOD_layoutbits::linkWithPicture($membership->Username) ?><br>
                <?=$membership->Username ?>
            </div><?php
        }
        ?><div style="clear:both;"></div><?php
    }
    
    public function setMeeting($meeting)
    {
        // extract memberlist information from the $meeting object
        $this->_meeting = $meeting;
    }
}





?>
