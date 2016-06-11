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
<div id="teaser" class="page-teaser clearfix">
  <h1 class="slogan fade in  hidden-xs"><span id="BeWelcome" >BeWelcome</span> <span id="opendoor"> - <?php echo $words->get('IndexPageTeaserOpenDoor');?></span></h1>
  <div class="subcolumns">
    <div class="c66l hidden-xs">
        <p class="sub-slogan"><?php echo $words->get('IndexPageTagline');?></p>
        <p class="clearfix"><a class="btn btn-primary" role="button" href="tour"><?php echo $words->get('tour_take');?></a></p>
        <!-- bootstrap carousel !-->
        <div id="carousel-example-generic" class="carousel" data-ride="carousel">
  <!-- Wrapper for slides -->
  <div class="carousel-inner">
    <div class="item active">
      <img src="images/startpage/octobertales_01.jpg" alt="<?php echo $words->getSilent('SlideImage1');?>">
    </div>
    <div class="item">
      <img src="images/startpage/paivisanteri_01.jpg" alt="<?php echo $words->getSilent('SlideImage2');?>">
    </div>
    <div class="item">
      <img src="images/startpage/sitatara_01.jpg" alt="<?php echo $words->getSilent('SlideImage3');?>">
    </div>
    <div class="item">
      <img src="images/startpage/smila_01.jpg" alt="<?php echo $words->getSilent('SlideImage4');?>">
    </div>
    <div class="item">
      <img src="images/startpage/smila_02.jpg" alt="<?php echo $words->getSilent('SlideImage5');?>">
    </div>
    <div class="item">
      <img src="images/startpage/octobertales_02.jpg" alt="<?php echo $words->getSilent('SlideImage6');?>">
    </div>
    <div class="item">
      <img src="images/startpage/sitatara_02.jpg" alt="<?php echo $words->getSilent('SlideImage7');?>">
    </div>

  </div>
</div>
<p class="small photodesc" style="color: #999; padding-top: 0.5em"><?=$words->get('StartPageNewListofPhotographers');?>: paivisanteri (PD), mikael, OctoberTales, sitatara, smila (CC)</p>
    </div> <!-- c66l -->

    <div class="c33r">
      <?php
             $login_widget = $this->createWidget('LoginFormWidget');
            $login_widget->render();
      ?>
      </div> <!-- c50r -->
    </div> <!-- subcolumns -->
</div> <!-- teaser -->
