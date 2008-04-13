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
 * meetings pages
 *
 * @package meetings
 * @author BeVolunteer - Toni (mahouni) (based on lemon-head`s groups application)
 */

class MeetingsAppBasePage extends RoxPageView
{
    protected function leftSidebar()
    {
        ?><h3>Last visited meetings</h3>
        <ul>
        <?php
        $last_visited = $this->getModel()->getLastVisited();
        foreach ($last_visited as $meeting) {
            if ($meeting) {
                ?><li><a href="meetings/<?=$meeting->getData()->id ?>"><?=$meeting->getData()->name ?></a></li>
                <?php
            }
        }
        ?></ul><?php
        ?><h3>My meetings</h3>
        <ul>
        <?php
        $my_meetings = $this->getModel()->getMyMeetings();
        foreach ($my_meetings as $meeting_data) {
            if ($meeting_data) {
                ?><li><a href="meetings/<?=$meeting_data->id ?>"><?=$meeting_data->name ?></a></li>
                <?php
            }
        }
        ?></ul><?php
    }
}


//------------------------------------------------------------------------------------
/**
 * base class for all pages in the meetings system,
 * which don't belong to one specific meeting.
 *
 */
class MeetingsBasePage extends MeetingsAppBasePage
{
    protected function getSubmenuItems()
    {
        return array(
            array('overview', 'meetings', 'Overview'),
            array('new', 'meetings/new', 'Create'),
        );
    }
    
}


//------------------------------------------------------------------------------------
/**
 * This page shows an overview of the meetings in bw,
 * with search, my meetings, etc
 *
 */
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
        $i_kat=999;
        $datekategory="";
        $kat_def=777;
        $i_date=999;
        ?><?php /*<div>
        <h3>Search Meetings</h3>
        <form>
        <input><input type="submit" value="Find"><br>
        </form>
        <h3>Create new meetings</h3>
        <div><span class="button"><a href="meetings/new">New meeting</a></span></div>
        <?php 
        $my_meetings = $this->getModel()->getMyMeetings();
        if (!empty($my_meetings)) {
            ?><h3>My Meetings</h3><?php
            foreach($this->getModel()->getMyMeetings() as $meeting_data) {
                ?><div>
                <a href="meetings/<?=$meeting_data->id ?>"><?=$meeting_data->name ?></a>
                </div><?php
            }
        }
        ?>
        </div> */?>
        <div style="float:right"><span class="button"><a href="meetings/new">New meeting</a></span></div>
        <h2>Meeting List</h2>
        <?php
        foreach($this->getModel()->getMeetings() as $meeting_data) {
            switch ($meeting_data->calkat) {
	        case 0:
		    $kat_def=0;
                    $datekategory="heute :: aujourd`hui :: today :: oggi :: hoje";
		    break;
		case 1:
		     $kat_def=1;
                     $datekategory="morgen :: demain :: tomorrow :: domani :: amanha";
		    break;
		case ($meeting_data->calkat>1 AND $meeting_data->calkat<7):
		     $kat_def=2;
		     $datekategory="diese woche :: cette semaine :: this week ::questa settimana :: esta semana";
		    break;
		case ($meeting_data->calkat>=7 AND $meeting_data->calkat<30):
		     $kat_def=3;
         	     $datekategory="diesen monat :: ce mois :: this month :: questo mese";
		    break;
	        case ($meeting_data->calkat>=30):
		     $kat_def=4;
		     $datekategory="dieses jahr :: cette année :: this year :: questo anno";
		    break;
	    }
            if ($kat_def!=$i_kat) {
		?><h3>  ::  <?=$datekategory?> ::</h3><?php
                $i_kat=$kat_def;
	    }
            if ($meeting_data->date_form!=$i_date) {
	        ?><h4> <?=$meeting_data->date_form?> </h4><?php
	        $i_date=$meeting_data->date_form;
	    }
	    if ($meeting_data->minstatus>0) {
                $status_m="<img src=\"images/rot.gif\" alt=\"not confirmed yet\">";
	    } else {
	        if ($meeting_data->maxstatus>=1) {
		    $status_m="<img src=\"images/gruen.jpg\" alt=\" confirmed\">";
		} else {
		    $status_m="<object data=\"images/orange.gif\" type=\"image/gif\" >crowded</object>";
		}
	    }

            ?><div>
            <h5><?=$meeting_data->time_form?> -- <a href="meetings/<?=$meeting_data->id ?>"><?=$meeting_data->name ?></a> -- <?=$status_m?> </h5>
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


//------------------------------------------------------------------------------------
/**
 * This page allows to create a new meeting
 *
 */
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
        <?php /* ?>
        <h3>Meeting options</h3>
        Tools:<br>
        <input type="checkbox" checked> Meeting forum<br>
        <input type="checkbox"> Meeting blog<br>
        <br>
        <?php */ ?>
        <h3>Who can join</h3>
        <input type="radio" checked> Any BeWelcome member<br>
        <input type="radio"> Any BeWelcome member, approved by moderators<br>
        <input type="radio"> Only invited BeWelcome members<br>
        <input type="radio"> Noone can join (it's not really a meeting)<br>
        <br>
        <h3>Create it now!</h3>
        <input type="submit" value="Create"><br>
        </form> 
        <?php
    }
    
    protected function getSubmenuActiveItem()
    {
        return 'new';
    }
}





?>
