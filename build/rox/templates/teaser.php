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
    <div class="c66l">
      <div class="subcl">

          <h1 class="slogan"><span id="something" ><?php echo $words->get('IndexPageTeaserReal1a');?></span> <span id="real" ><?php echo $words->get('IndexPageTeaserReal1b');?></span>&nbsp;</h1>
          <h2><?php echo $words->get('IndexPageTeaserReal2');?></h2>
          
          <? /*
            <div class="video-embedded">
              <!--<img src="images/misc/video-placeholder.png">-->
              <object width="306" height="172"><param name="allowfullscreen" value="true" /><param name="allowscriptaccess" value="always" /><param name="movie" value="http://vimeo.com/moogaloop.swf?clip_id=3545292&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" /><embed src="http://vimeo.com/moogaloop.swf?clip_id=3545292&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=00ADEF&amp;fullscreen=1" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="306" height="172"></embed></object>
            </div>
            */ ?>
            <div id="slideshow-content" class="float_left" >
                <div class="slide" id="slide1">
                <img src="images/startpage/pablod_01.jpg" style="width: 360px; height: 240px" alt="<?php echo $words->getSilent('SlideImage1');?>" />
                </div>
                <div class="slide" id="slide2" style="display: none;">
                <img src="images/startpage/Olga_Kruglova_01.jpg" style="width: 360px; height: 240px" alt="<?php echo $words->getSilent('SlideImage2');?>" />
                </div>
                <div class="slide" id="slide3" style="display: none;">
                <img src="images/startpage/pablod_02.jpg" style="width: 360px; height: 240px" alt="<?php echo $words->getSilent('SlideImage3');?>" />
                </div>
                <div class="slide" id="slide4" style="display: none;">
                <img src="images/startpage/Olga_Kruglova_02.jpg" style="width: 360px; height: 240px" alt="<?php echo $words->getSilent('SlideImage4');?>" />
                </div>
                <div class="slide" id="slide5" style="display: none;">
                <img src="images/startpage/pablod_03.jpg" style="width: 360px; height: 240px" alt="<?php echo $words->getSilent('SlideImage5');?>" />
                </div>
            </div>
            
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

          <div class="video-desc">
            <p><?//=$words->get('IndexPageVideoDesc','username') //TODO: Fix the 'username' to something dynamic ?>
            <a class="button" href="tour" onclick="this.blur();"><?php echo $words->get('tour_take');?></a>
            <br /><br />
            </p>

            <p class="small photodesc" style="color: #999;">
                all pictures (cc)
                <?=$words->get('StartPageListofPhotographers');?>
            </p>
          </div>

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
                start_slideshow(1, 5, 10000);
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
