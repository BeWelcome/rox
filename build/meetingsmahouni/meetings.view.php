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
 * meetings view
 *
 * @package meetings
 * @author BeVolunteer - Toni (mahouni) (based on lemon-head`s groups application)
 */

class MeetingsBasePage extends RoxPageView
{
    protected function leftSidebar()
    {
        ?><h3>Meetings Overview sidebar</h3><?php
    }
    
    protected function getSubmenuItems()
    {
        return array(
            array('overview', 'meetings', 'Overview'),
            array('new', 'meetings/new', 'Create'),
        );
    }
    
    
}

class MeetingsOverviewPage extends MeetingsBasePage
{
    
    protected function teaserContent()
    {
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="meetings">Meetings</a></h1>
        </div>
        </div><?php
    }
    
    protected function column_col3()
    {
        ?><div>
        <h3>Search Meetings</h3>
        <form>
        <input><input type="submit" value="Find"><br>
        </form>
        <h3>Create new meetings</h3>
        <div><span class="button"><a href="meetings/new">New meeting</a></span></div>
        <h3>My Meetings</h3>
        <?php
        if (!isset($_SESSION['IdMember'])) {
            // nothing
        } else foreach($this->getModel()->getMeetingsForMember($_SESSION['IdMember']) as $meeting_data) {
            ?><div>
            <a href="meetings/<?=$meeting_data->id ?>"><?=$meeting_data->name ?></a>
            </div><?php
        }
        ?>
        </div>
        <div style="float:right"><span class="button"><a href="meetings/new">New meeting</a></span></div>
        <h3>Meeting List</h3>
        <?php
        foreach($this->getModel()->getMeetings() as $meeting_data) {
            ?><div>
            <a href="meetings/<?=$meeting_data->id ?>"><?=$meeting_data->name ?></a>
            </div><?php
        }
        ?>
        </div><?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'overview';
    }
}

class MeetingsCreationPage extends MeetingsBasePage
{
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="meetings">Meetings</a> &raquo; <a href="meetings/new">New</a></h1>
        </div>
        </div><?php
    }
    
    protected function column_col3()
    {
        ?>
        <h3>Create a new Meeting</h3>
        <form>
        Name:<br>
        <input/><br><br>
        Description:<br>
        <textarea cols="50" rows="5""></textarea><br><br>
        Tools:<br>
        <input type="checkbox" checked> Meeting forum<br>
        <input type="checkbox"> Meeting wikipage<br>
        <input type="checkbox"> Meeting blog<br>
        <br>
        <input type="submit" value="Create"><br>
        </form> 
        <?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'new';
    }
}


class MeetingBasePage extends RoxPageView
{
    private $_meeting = false;
    private $_members = false;
    
    public function setMeeting($meeting) {
        $this->_meeting = $meeting;
    }
    
    protected function getMeeting() {
        return $this->_meeting;
    }
    
    protected function teaserContent()
    {
        // &gt; or &raquo; ?
        ?><div id="teaser" class="clearfix">
        <div id="teaser_l1"> 
        <h1><a href="meetings">Meetings</a> &raquo; <a href="meetings/<?=$this->getMeeting()->getData()->id ?>"><?=$this->getMeeting()->getData()->name ?></a></h1>
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
        return array(
            array('start', 'meetings/'.$meeting_id, 'Start'),
            array('members', 'meetings/'.$meeting_id.'/members', 'Members'),
        );
    }
    
    protected function leftSidebar()
    {
        ?><h3>Meeting sidebar</h3><?php
    }
}

class MeetingStartPage extends MeetingBasePage
{
    protected function column_col3()
    {
        ?><h3>Meeting Description</h3>
        <div><pre><?php
        print_r($this->getMeeting()->getData());
        ?></pre></div>
        <h3>Meeting Members</h3>
        <div><?php
        $memberlist_widget = new MeetingMemberlistWidget();
        $memberlist_widget->setMeeting($this->getMeeting());
        $memberlist_widget->render();
        ?></div>
        <h3>Meeting Forum</h3>
        <div><pre><?php
        $forums_widget = new MeetingForumWidget();
        $forums_widget->setMeeting($this->getMeeting());
        $forums_widget->render();
        ?></pre></div><?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'start';
    }
}

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

class MeetingMemberlistWidget  // extends MemberlistWidget?
{
    private $_meeting;
    
    public function render()
    {
        $memberships = $this->_meeting->getMemberships(10);
        foreach ($memberships as $membership) {
            ?><div style="float:left; border:1px solid #fec;">
            :: <?=$membership->Username ?>
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
