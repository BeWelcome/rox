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
    <h3><?php echo $words->get('tour_openness')?></h3>
        
    <h4><?php echo $words->getFormatted('tour_openness_title1')?></h4>
    <p><?php echo $words->getFormatted('tour_openness_text1')?></p>
    <div class="clearfix">
        <div class="float_left" style="padding:10px">
            <iframe width="400" height="300" src="http://www.youtube-nocookie.com/embed/aRS_wG4ZN4k?rel=0" frameborder="0" allowfullscreen></iframe>
        </div>
        <p><?php echo $words->getFormatted('tour_openness_videotext','<a href="http://en.wikipedia.org/wiki/unconference">','</a>')?></p>
        <h4><?php echo $words->getFormatted('tour_openness_title2')?></h4>
        <p><?php echo $words->getFormatted('tour_openness_text2')?></p>
    </div>
    
    <h4><?php echo $words->getFormatted('tour_openness_title3')?></h4>
    <p><?php echo $words->getFormatted('tour_openness_text3')?></p>
    <h4><?php echo $words->getFormatted('tour_gosignup','<a href="signup">','</a>')?></h4>
    <a class="button" href="signup" onclick="this.blur();" style="margin-bottom: 20px"><span><?php echo $words->getFormatted('signup_now')?></span></a>
</div>
        
