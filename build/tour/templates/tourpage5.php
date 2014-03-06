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
$words = new MOD_words();
?>
<div id="tour">
    <h3><?php echo $words->get('tour_trips')?></h3>
        
    <h4><?php echo $words->getFormatted('tour_trips_title1')?></h4>
    <div class="clearfix">
        <img src="images/tour/trips-example.png" class="float_left" alt="trips" />
        <p><?php echo $words->getFormatted('tour_trips_text1')?></p>
    </div>

    <h4><?php echo $words->getFormatted('tour_trips_title2')?></h4>
    <p><?php echo $words->getFormatted('tour_trips_text2')?></p>
    <h4><?php echo $words->getFormatted('tour_trips_title3')?></h4>
    <p><?php echo $words->getFormatted('tour_trips_text3')?></p>
    <a class="bigbutton" href="tour/maps" onclick="this.blur();" style="margin-bottom: 20px"><span><?php echo $words->getFormatted('tour_goNext')?> &raquo; <?php echo $words->getFormatted('tour_link_maps')?></span></a> 
</div>
        
