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
    <h1><?php echo $words->get('tour_maps')?></h1>

    <h2><?php echo $words->getFormatted('tour_maps_title1')?></h2>
    <p><?php echo $words->getFormatted('tour_maps_text1')?></p>

    <div class="floatbox">
        <img src="images/tour/map2.png" class="float_left" alt="maps" />
        <h2><?php echo $words->getFormatted('tour_maps_title2')?></h2>
        <p><?php echo $words->getFormatted('tour_maps_text2')?></p>
    </div>

    <h2><?php echo $words->getFormatted('tour_maps_title3')?></h2>
    <p><?php echo $words->getFormatted('tour_maps_text3')?></p>
    <h2><a class="bigbutton" href="tour/openness" onclick="this.blur();" style="margin-bottom: 20px"><span><?php echo $words->getFormatted('tour_goNext')?> &raquo;</span></a> <?php echo $words->getFormatted('tour_openness')?></h2>
</div>

