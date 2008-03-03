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

<h2><?php echo $words->get('DonateTitle1');?></h2>
<div class="subcolumns">
  <div class="c50l">
    <div class="subcl">
    <a name="why"></a>
	<h3><?php echo $words->get('Donate_Why');?></h3>
	<p><?php echo $words->getFormatted('Donate_WhyText','<a href="/bw/feedback.php">','</a>')?></p>
    
    <a name="tax"></a>
    <h3><?php echo $words->get('Donate_How'); ?></h3>
    <p><?php echo $words->get('Donate_HowText'); ?></p>
    </div>
   </div>

  <div class="c50r">
    <div class="subcr">
            <a name="tax"></a>
            <h3><?php echo $words->get('Donate_Tax'); ?></h3>
            <p><?php echo $words->get('Donate_TaxText'); ?></p>
            <a name="transparency"></a>
            <h3><?php echo $words->get('Donate_Transparency'); ?></h3>
            <p><?php echo $words->getFormatted('Donate_TransparencyText','<a href="http://www.bevolunteer.org/joomla/index.php/Donate!?Itemid=54&option=com_civicrm">','</a>'); ?></p>		  
    </div>
  </div>
</div>


<h3><?php echo $words->get('Donate_FurtherInfo'); ?></h3>
<p><?php echo $words->get('Donate_FurtherInfoText','<a href="http://bevolunteer.org/wiki"','</a>');?></p>
	 