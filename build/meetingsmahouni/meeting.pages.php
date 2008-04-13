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
 * meeting pages
 *
 * @package meetings
 * @author BeVolunteer - Toni (mahouni) (based on lemon-head`s groups application)
 */

//------------------------------------------------------------------------------------
/**
 * base class for all pages showing a meeting
 *
 */
class MeetingBasePage extends MeetingsAppBasePage
{
    private $_meeting = false;
    private $_members = false;
    
    public function setMeeting($meeting) {
        $this->_meeting = $meeting;
    }
    
    protected function getMeetingTitle() {
        return $this->getWords()->getBuffered(
            'Meeting_'.$this->_meeting->getData()->name
        );
    }
    
    protected function getMeetingDescription() {
        return $this->getWords()->getBuffered(
            'MeetingDesc_'.$this->_meeting->getData()->name
        );
    }
    
    protected function getMeetingId() {
        return $this->_meeting->getData()->id;
    }
    
    protected function isMeetingMember() {
        if (!isset($_SESSION['IdMember'])) {
            return false;
        } else {
            return $this->_meeting->isMember($_SESSION['IdMember']);
        }
    }
    
    protected function getMeeting() {
        return $this->_meeting;
    }
    
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="meetings">Meetings</a> &raquo; <a href="meetings/<?=$this->getMeeting()->getData()->id ?>"><?=$this->getMeetingTitle() ?></a></h1>
        </div>
        </div><?php
    }
    
    protected function getTopmenuActiveItem()
    {
        return 'meetings';
    }
    
    protected function getSubmenuItems()
    {
        $meeting_id = $this->getMeeting()->getData()->id;
        $items = array();
        $items[] = array('start', 'meetings/'.$meeting_id, $this->getMeeting()->getData()->name);
        $items[] = array('members', 'meetings/'.$meeting_id.'/members', 'Members');
        if (!$this->isMeetingMember()) {
            $items[] = array('join', 'meetings/'.$meeting_id.'/join', 'Join');
        } else {
            $items[] = array('settings', 'meetings/'.$meeting_id.'/settings', 'Member settings');
            $items[] = array('leave', 'meetings/'.$meeting_id.'/leave', 'Leave');
        }
        return $items;
    }
    
}

//------------------------------------------------------------------------------------
/**
 * This page shows an overview of the meeting
 *
 */
class MeetingStartPage extends MeetingBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        
        $memberlist_widget = new MeetingMemberlistWidget();
        $memberlist_widget->setMeeting($this->getMeeting());
        
        $forums_widget = new MeetingForumWidget();
        $forums_widget->setMeeting($this->getMeeting());
        
        ?><h3>Meeting Description</h3>        
        <div><pre><?php print_r($this->getMeeting()->getData()); ?></pre></div><?php
        ?>
        <h3>Meeting Members</h3>
        <div><?php $memberlist_widget->render() ?></div>
        <h3>Meeting Forum</h3>
        <div><?php $forums_widget->render() ?></div><?php
    }
    
    protected function getSubmenuActiveItem() {
        return 'start';
    }
}


//------------------------------------------------------------------------------------
/**
 * This page asks if the user wants to join the meeting
 *
 */
class MeetingJoinPage extends MeetingBasePage
{
    protected function column_col3()
    {
        ?><h3>Join the meeting "<?=$this->getMeetingTitle() ?>" ?</h3>
        Your choice.<br>
        <span class="button"><a href="meetings/<?=$this->getMeetingId() ?>/join/yes">Join</a></span>
        <span class="button"><a href="meetings/<?=$this->getMeetingId() ?>/join/no">Cancel</a></span>
        <?php
    }
    
    protected function getSubmenuActiveItem() {
        return 'join';
    }
}


//------------------------------------------------------------------------------------
/**
 * This page asks if the user wants to leave the meeting
 *
 */
class MeetingLeavePage extends MeetingBasePage
{
    protected function column_col3()
    {
        ?><h3>Leave the meeting "<?=$this->getMeetingTitle() ?>" ?</h3>
        Your choice.<br>
        <span class="button"><a href="meetings/<?=$this->getMeetingId() ?>/leave/yes">Leave</a></span>
        <span class="button"><a href="meetings/<?=$this->getMeetingId() ?>/leave/no">Cancel</a></span>
        <?php
    }
    
    protected function getSubmenuActiveItem() {
        return 'leave';
    }
}


class MeetingMembersPage extends MeetingBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        ?><h3>Meeting Description</h3>
        <?=$this->getMeetingDescription() ?><br>
        <?php
        /* ?><div><pre><?php print_r($this->getMeeting()->getData()); ?></pre></div><?php */
        ?>
        <h3>Meeting Members</h3>
        <div><?php
        $members = $this->getMeeting()->getMembers();
        foreach ($members as $member) {
            ?><div style="margin:2px; border:1px solid #eee; padding:2px;">
            <div style="float:left; padding: 4px">
            <?=MOD_layoutbits::linkWithPicture($member->Username) ?>
            </div>
            <div style="margin-left:80px">
            <strong><?=$member->Username ?></strong><br>
            I joined this meeting because...
            </div>
            <div style="clear:both; margin:2px"></div>
            </div>
            <?php
        }
        ?></div>
        <?php
    }
    
    protected function getSubmenuActiveItem() {
        return 'members';
    }
    
}


?>
