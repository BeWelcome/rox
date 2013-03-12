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
     * @author shevek
     */

    /**
     * This page list all future events
     *
     * @package Apps
     * @subpackage Events
     */
class EventsListPage extends EventsBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        echo '<div class="row"><h3>Upcoming Events</h3>';
        echo '<table class="forumsboardthreads">';
        echo '<tr>';
        echo '<th>Category</th>';
        echo '<th>Name</th>';
        echo '<th>Date</th>';
        echo '<th>Place</th>';
        echo '<th>Attendees</th>';
        echo '<th>Organizer</th>';
        echo '</tr>';
        $events = array(
            array('event-category' => '<img src="images/icons/maybe.png" width="16" height="16" alt="" /> <img src="images/icons/nosorry.png" width="16" height="16" alt="" />', 'event-name' => '<a href="events/show/10">Lorem ipsum dolor</a>', 'event-date' => 'Jan, 10 2013<br> 11:00am - 1:00pm', 'event-address' => 'Somewhere', 'event-attendees' => '12', 'event-organizer' => '<a href="members/someone">someone</a><br>Created Jan, 07 2013'),
            array('event-category' => '<img src="images/icons/yesicanhost.png" width="16" height="16" alt="" /> <img src="images/icons/maybe.png" width="16" height="16" alt="" /> <img src="images/icons/maybe.png" width="16" height="16" alt="" /><br /><img src="images/icons/maybe.png" width="16" height="16" alt="" /> <img src="images/icons/maybe.png" width="16" height="16" alt="" /> <img src="images/icons/maybe.png" width="16" height="16" alt="" /><br /><img src="images/icons/maybe.png" width="16" height="16" alt="" /> <img src="images/icons/maybe.png" width="16" height="16" alt="" /> <img src="images/icons/maybe.png" width="16" height="16" alt="" />', 'event-name' => '<a href="events/show/10">Some really long name for an event. We should possible cut this down to 64 chars.</a>', 'event-date' => 'Jan, 10 2013<br> 11:00am - 1:00pm', 'event-address' => 'over the', 'event-attendees' => '1102', 'event-organizer' => '<a href="members/someone">someone</a><br>Created Dec, 12 2012'),
            array('event-category' => '<img src="images/icons/wheelchair.png" width="16" height="16" alt="" />', 'event-name' => '<a href="events/show/10">consetetur sadipscing elitr</a>', 'event-date' => 'Jan, 10 2013<br> 11:00am - 1:00pm', 'event-address' => 'Rainbow', 'event-attendees' => '3', 'event-organizer' => '<a href="members/someone">someone</a><br>Created Jan, 01 2013')
            );
        for($ii = 0; $ii < 5; $ii++)
        {
            if ($ii % 2) {
                $row = '<tr class="highlight">';
            } else {
                $row = '<tr class="blank">';
            }
            $rowcontent = $events[$ii % 3];
            foreach($rowcontent as $style => $item) {
                $row .= '<td class="' . $style . '" style="vertical-align: top;">' . $item . '</td>';
            }
            $row .= '</tr>';
            echo $row;
        }
        echo '</table>';
        echo '</div>';
        echo '<div id="boardnewtopicbottom"><span class="button"><a href="events/create">New event</a></span></div>';
        echo '
<div class="pages">
	<ul>
		<li>

<a class="off">«</a>		</li>
<li class="current"><a class="off">1</a></li><li><a href="forums/page2/">2</a></li><li><a href="forums/page3/">3</a></li><li class="sep">...</li><li><a href="forums/page10/">10</a></li><li><a href="forums/page11/">11</a></li>		<li>
<a class="off">»</a>		</li>
	</ul>
</div></div>'; 
    }
    
    protected function getSubmenuActiveItem() {
        return 'list';
    }
}


