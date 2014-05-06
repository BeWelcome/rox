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
  <h1 class="slogan"><span id="BeWelcome" >BeWelcome</span> <span id="opendoor"> - <?php echo $words->get('IndexPageTeaserOpenDoor');?></span></h1>
  <div class="subcolumns">
    <div class="c66l">
      <div class="subcl">
          <h2 style="color:red"><strong>Because of the ongoing server migration message notifications via email will be delayed. Sorry for the inconvenience.</strong></h2>
          <h2 style="padding-bottom:0em; width:560px"><?php echo $words->get('IndexPageTagline');?></h2>
          <p style="padding-bottom:0.5em; width:560px; text-align:right;"><a class="button2" href="tour" onclick="this.blur();"><?php echo $words->get('tour_take');?></a> </p>
          <div id="slideshow-content">
                <div class="slide" id="slide1">
                <img src="images/startpage/octobertales_01.jpg" style="width: 560px; height: 240px" alt="<?php echo $words->getSilent('SlideImage1');?>" />
                </div>
                <div class="slide" id="slide2" style="display: none;">
                <img src="images/startpage/paivisanteri_01.jpg" style="width: 560px; height: 240px" alt="<?php echo $words->getSilent('SlideImage2');?>" />
                </div>
                <div class="slide" id="slide3" style="display: none;">
                <img src="images/startpage/sitatara_01.jpg" style="width: 560px; height: 240px" alt="<?php echo $words->getSilent('SlideImage3');?>" />
                </div>
                <div class="slide" id="slide4" style="display: none;">
                <img src="images/startpage/smila_01.jpg" style="width: 560px; height: 240px" alt="<?php echo $words->getSilent('SlideImage4');?>" />
                </div>
                <div class="slide" id="slide5" style="display: none;">
                <img src="images/startpage/smila_02.jpg" style="width: 560px; height: 240px" alt="<?php echo $words->getSilent('SlideImage5');?>" />
                </div>
                <div class="slide" id="slide6" style="display: none;">
                <img src="images/startpage/octobertales_02.jpg" style="width: 560px; height: 240px" alt="<?php echo $words->getSilent('SlideImage6');?>" />
                </div>
                <div class="slide" id="slide7" style="display: none;">
                <img src="images/startpage/sitatara_02.jpg" style="width: 560px; height: 240px" alt="<?php echo $words->getSilent('SlideImage7');?>" />
                </div>
            </div>
            <p class="small photodesc" style="color: #999; padding-top: 0.5em"><?=$words->get('StartPageNewListofPhotographers');?>: paivisanteri (PD), mikael, OctoberTales, sitatara, smila (CC)</p>

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
            
        <script type="text/javascript">
            <!--
            function realeffect() {
                new Effect.toggle('opendoor', 'appear', {duration: 2})
            }
            $('opendoor').hide();
            $('BeWelcome').hide();
            window.onload = function () {
                new Effect.toggle('BeWelcome', 'appear', {duration: 2});
                setTimeout('realeffect()',2000);
                start_slideshow(1, 7, 7000);
            };

        // -->
        </script>
      </div> <!-- subcl -->
    </div> <!-- c66l -->

    <div class="c33r">
      <div class="subcr">
      <?php
             $login_widget = $this->createWidget('LoginFormWidget');
            $login_widget->render();
      ?>
        </div> <!-- subcr -->
      </div> <!-- c50r -->
    </div> <!-- subcolumns -->
</div> <!-- teaser -->
