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
     * This page list all future Activities
     *
     * @package Apps
     * @subpackage Activities
     */
class ActivitiesFindPage extends ActivitiesBasePage
{
    protected function column_col3()
    {
        $words = $this->getWords();
        echo '<div class="bw-row"><h3>Upcoming Activities</h3>';
        echo '<table class="forumsboardthreads">';
        echo '<tr>';
        echo '<th>Category</th>';
        echo '<th>Name</th>';
        echo '<th>Date</th>';
        echo '<th>Place</th>';
        echo '<th>Attendees</th>';
        echo '<th>Organizer</th>';
        echo '</tr>';
        $Activities = array(
            array('<img src="images/icons/maybe.png" width="16" height="16">', 'Lorem ipsum dolor', 'Jan, 10 2013<br> 11:00am - 1:00pm', 'Somewhere', '12', '<a href="members/someone">someone</a><br>Created Jan, 07 2013'),
            array('<img src="images/icons/maybe.png" width="16" height="16">', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren,', 'Jan, 10 2013<br> 11:00am - 1:00pm', 'over the', '1102', '<a href="members/someone">someone</a><br>Created Dec, 12 2012'),
            array('<img src="images/icons/maybe.png" width="16" height="16">', 'Lorem ipsum dolor', 'Jan, 10 2013<br> 11:00am - 1:00pm', 'Rainbow', '3', '<a href="members/someone">someone</a><br>Created Jan, 01 2013')
            );
        for($ii = 0; $ii < 5; $ii++)
        {
            if ($ii % 2) {
                $row = '<tr class="highlight">';
            } else {
                $row = '<tr class="blank">';
            }
            $rowcontent = $Activities[$ii % 3];
            foreach($rowcontent as $item) {
                $row .= '<td>' . $item . '</td>';
            }
            $row .= '</tr>';
            echo $row;
        }
        echo '</table>';
        echo '</div>';
        echo '<div id="boardnewtopicbottom"><a class="button" role="button" href="Activities/create">New event</a></div>';
        echo '
<div class="pages clearfix">
	<ul>
		<li>

<a class="off">«</a>		</li>
<li class="current"><a class="off">1</a></li><li><a href="forums/page2/">2</a></li><li><a href="forums/page3/">3</a></li><li class="sep">...</li><li><a href="forums/page10/">10</a></li><li><a href="forums/page11/">11</a></li>		<li>
<a class="off">»</a>		</li>
	</ul>
</div>';    }
    
    protected function getSubmenuActiveItem() {
        return 'list';
    }
}


