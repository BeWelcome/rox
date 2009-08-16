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

<div id="teaser" class="clearfix">
  <div class="subcolumns">
    <div class="c62l">
      <div class="subcl">
        <h1 class="slogan"><span id="something" ><?php echo $words->get('IndexPageTeaserReal1a');?></span> <span id="real" ><?php echo $words->get('IndexPageTeaserReal1b');?></span>&nbsp;</h1>
        <h2><?php echo $words->get('IndexPageTeaserReal2');?></h2>
        <a class="bigbutton float_left" href="signup" onclick="this.blur();"><span><?php echo $words->get('signup_now');?></span></a>
        <a class="bigbutton float_left" href="tour" onclick="this.blur();"><span><?php echo $words->get('tour_take');?></span></a>
      </div> <!-- subcl -->
    </div> <!-- c50l -->

    <div class="c38r">
      <div class="subcr">
        <div id="slideshow-content">
            <div class="slide" id="slide1">
            <img src="images/tour/share4_small.jpg" alt="share" />
            </div>
            <div class="slide" id="slide2" style="display: none;">
            <img src="images/tour/syrien.jpg" alt="syria" />
            </div>
            <div class="slide" id="slide3" style="display: none;">
            <img src="images/tour/mountain1.jpg" alt="mountain" />
            </div>
            <div class="slide" id="slide4" style="display: none;">
            <img src="images/tour/river.jpg" alt="river" />
            </div>
            <div class="slide" id="slide5" style="display: none;">
            <img src="images/tour/dancing2.jpg" alt="dancing" />
            </div>
            <div class="slide" id="slide6" style="display: none;">
            <img src="images/tour/mountain2.jpg" alt="river" />
            </div>
            <div class="slide" id="slide7" style="display: none;">
            <img src="images/tour/people.jpg" alt="river" />
            </div>
            <div class="slide" id="slide8" style="display: none;">
            <img src="images/tour/people2.jpg" alt="river" />
            </div>
            <p class="small photodesc">
                (cc)
                <?=$words->get('StartPageListofPhotographers');?>
            </p>
            </div>
        </div> <!-- subcr -->
      </div> <!-- c50r -->
    </div> <!-- subcolumns -->



<script type="text/javascript">
    <!--
    function realeffect() {
        new Effect.toggle('real', 'appear', {duration: 2})
    }
    $('real').hide();
    $('something').hide();
    window.onload = function () {
        new Effect.toggle('something', 'appear', {duration: 2});
        setTimeout('realeffect()',2000);
        start_slideshow(1, 8, 10000);
    };

// -->
</script>

<script type="text/javascript">

    function start_slideshow(start_frame, end_frame, delay) {
        setTimeout(switch_slides(start_frame,start_frame,end_frame, delay), delay);
    }

    function switch_slides(frame, start_frame, end_frame, delay) {
        return (function() {
            Effect.Fade('slide' + frame);
            if (frame == end_frame) { frame = start_frame; } else { frame = frame + 1; }
            setTimeout("Effect.Appear('slide" + frame + "');", 950);
            setTimeout(switch_slides(frame, start_frame, end_frame, delay), delay + 950);
        })
    }

</script>
</div>
