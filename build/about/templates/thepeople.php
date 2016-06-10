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
write to the Free Software Foundation, Inc., 59 Temple PlaceSuite 330, 
Boston, MA  02111-1307, USA.

*/
$words = new MOD_words($this->getSession());
?>

<h2><?php echo $words->get("ThePeople") ?></h2>
<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">

            <h3><?php echo $words->get("ThePeople_Title1") ?></h3>
            <p><?php echo $words->get("ThePeople_Text1")?></p>
    
    </div>
   </div>

  <div class="c50r">
    <div class="subcr">
            <h3><?php echo $words->get("ThePeople_TitleInterviews") ?></h3>
            <p><?php echo $words->getFormatted('ThePeople_TextInterviews','<a href="http://www.bevolunteer.org/wiki/Interviews">','</a>')?></p>
		  
    </div>
  </div>
</div>
<div class="subcolumns">
  <div class="c25l">
   </div>
  <div class="c50l">
    <div class="subcl">
        <div class="thumbnail">
            <!-- bootstrap carousel !-->
            <div id="carousel-example-generic2" class="carousel" data-ride="carousel">
              <div class="carousel-inner">
                <div class="item active">
                  <img src="http://farm3.static.flickr.com/2200/2204242456_f2a726c103.jpg">
                </div>
                <div class="item">
                  <img src="http://farm3.static.flickr.com/2275/2204241012_756d5234e6.jpg">
                </div>
                <div class="item">
                  <img src="http://farm3.static.flickr.com/2157/2204319026_8764a0573a.jpg">
                </div>
                <div class="item">
                  <img src="http://farm3.static.flickr.com/2161/2206203954_9a511d50d2.jpg">
                </div>
                <div class="item">
                  <img src="http://farm3.static.flickr.com/2179/2204240888_d90054b31a.jpg">
                </div>
              </div>
            </div><!-- end: bootstrap carousel !-->
            <div class="caption">
                <p class="small"><?php echo $words->getFormatted('ThePeople_PictureText1','<a title="unconference: A meeting with a non-fixed schedule that can be changed all the time.">','</a>') ?>
                <br><?php echo $words->getFormatted('ThePeople_PictureText2','<a href="http://www.flickr.com/photos/22828233@N05/">','</a>') ?></p>
            </div>
        </div>
    </div>
   </div>
  <div class="c25r">
  </div>
</div>
<div class="center">
    <h3><?php echo $words->get("ThePeople_Title3") ?></h3>
    <p><?php echo $words->get("ThePeople_Text3")?></p>
</div>

	 