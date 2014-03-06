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
$words = new MOD_words();
?>

<h2><?php echo $words->get('GetActive') ?></h2>
<p><?php echo $words->get('GetActiveIntro') ?></p>

<h3><?php echo $words->get('GetActiveTitle1') ?></h3>
<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
        <div class="clearfix">
            <img class="float_left" src="images/icons/tango/32x32/application-x-php.png" alt="development" />
            <h4><?php echo $words->get('GetActiveDevTitle')?></h4>
            <p><?php echo $words->get('GetActiveDevText')?></p>
        </div> <!-- clearfix -->
        
        <div class="clearfix">
            <img class="float_left" src="images/icons/tango/32x32/applications-graphics.png" alt="design" />
            <h4><?php echo $words->get('GetActiveDesignTitle')?></h4>
            <p><?php echo $words->get('GetActiveDesignText')?></p>
        </div> <!-- clearfix -->
        
        <div class="clearfix">
            <img class="float_left" src="images/icons/tango/32x32/applications-science.png" alt="testing" />
            <h4><?php echo $words->get('GetActiveTestingTitle')?></h4>
            <p><?php echo $words->get('GetActiveTestingText')?></p>
        </div> <!-- clearfix -->
        
        <div class="clearfix">
          	<img class="float_left" src="images/icons/tango/32x32/donatek.png" alt="donation" />
            <h4><?php echo $words->get('GetActiveDonationTitle')?></h4>
            <p><?php echo $words->get('GetActiveDonationText')?></p>
        </div> <!-- clearfix -->
    </div>
   </div>

  <div class="c50r">
    <div class="subcr">
        <div class="clearfix">
            <img class="float_left" src="images/icons/tango/32x32/help-browser.png" alt="support" />
            <h4><?php echo $words->get('GetActiveSupportTitle')?></h4>
            <p><?php echo $words->get('GetActiveSupportText')?></p>
        </div> <!-- clearfix -->
        
        <div class="clearfix">
            <img class="float_left" src="images/icons/tango/32x32/languages.png" alt="translate" />
            <h4><?php echo $words->get('GetActiveTranslateTitle')?></h4>
            <p><?php echo $words->get('GetActiveTranslateText')?></p>
        </div> <!-- clearfix -->
        
        <div class="clearfix">
            <img class="float_left" src="images/icons/tango/32x32/system-users.png" alt="local" />
            <h4><?php echo $words->get('GetActiveLocalTitle')?></h4>
            <p><?php echo $words->get('GetActiveLocalText')?></p>
        </div> <!-- clearfix -->
        
        <div class="clearfix">
            <img class="float_left" src="images/icons/tango/32x32/megaphone.png" alt="PR" />
            <h4><?php echo $words->get('GetActivePRTitle')?></h4>
            <p><?php echo $words->get('GetActivePRText')?></p>
        </div> <!-- clearfix -->
    </div>
  </div>
</div>


