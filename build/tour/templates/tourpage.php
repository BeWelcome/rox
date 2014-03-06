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
        <h3><?php echo $words->get('tourpage')?></h3>
        
        <h4><?php echo $words->getFormatted('tourpage_title1')?></h4>
        
        <div class="subcolumns">
          <div class="c50l">
            <div class="subcl">
                <h4><?php echo $words->getFormatted('tour_link_share')?></h4>
                <div class="clearfix">
                <a href="tour/share"><img src="images/tour/arrow_share_small.png" class="float_right" alt="share" /></a>
                <p><?php echo $words->getFormatted('tourpage_text2')?></p>
                </div>
                
                <h4><?php echo $words->getFormatted('tour_link_meet')?></h4>
                <div class="clearfix">
                <a href="tour/meet"><img src="images/tour/arrow_world_small.png" class="float_right" alt="meet" /></a>
                <p><?php echo $words->getFormatted('tourpage_text3')?></p>
                </div>
                
                <h4><?php echo $words->getFormatted('tour_link_trips')?></h4>
                <div class="clearfix">
                <a href="tour/trips"><img src="images/tour/arrow_plan_small.png" class="float_right" alt="plan trips" /></a>
                <p><?php echo $words->getFormatted('tourpage_text4')?></p>
                </div>
            </div> <!-- subcl -->
          </div> <!-- c50l -->

          <div class="c50r">
            <div class="subcr">
                <h4><?php echo $words->getFormatted('tour_link_maps')?></h4>
                <div class="clearfix">
                <a href="tour/maps"><img src="images/tour/arrow_maps_small.png" class="float_right" alt="maps" /></a>
                <p><?php echo $words->getFormatted('tourpage_text5')?></p>
                </div>
                
                <h4><?php echo $words->getFormatted('tour_link_openness')?></h4>
                <div class="clearfix">
                <a href="tour/openness"><img src="images/tour/arrow_door_small.png" class="float_right" alt="open door" /></a>
                <p><?php echo $words->getFormatted('tourpage_text6')?></p>
                </div>
            </div> <!-- subcr -->
          </div> <!-- c50r -->
        </div> <!-- subcolumns index_row1 -->
        
        <a class="bigbutton" href="tour/share" onclick="this.blur();" style="margin-bottom: 20px"><span><?php echo $words->getFormatted('tour_start')?></span></a>
    </div>
        
