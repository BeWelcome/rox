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
        <h1><?php echo $words->get('tour_openness')?></h1>
        
        <h2><?php echo $words->getFormatted('tour_openness_title1')?></h2>
        <p><?php echo $words->getFormatted('tour_openness_text1')?></p>
        
        <h2><?php echo $words->getFormatted('tour_openness_title2')?></h2>
        <p><?php echo $words->getFormatted('tour_openness_text2')?></p>
        <p class="floatbox"><img src="images/tour/trac.jpg" class="framed float_left"></p>
        <h2><?php echo $words->getFormatted('tour_openness_title3')?></h2>
        <p><?php echo $words->getFormatted('tour_openness_text3')?></p>
        <div class="floatbox" style="padding-top: 30px">
        <embed class="float_left" style="width:400px; height:326px;" id="VideoPlayback"
        type="application/x-shockwave-flash" src="http://video.google.com/googleplayer.swf?docId=-5605653070159143554&hl=nl
        " flashvars=""> </embed>
        <div class="float_left" style="width:100px; padding:20px">
        <p><?php echo $words->getFormatted('tour_openness_videotext','<a href="http://en.wikipedia.org/wiki/unconference">','</a>')?></p>
        
        </div>
        </div>
    </div>
        