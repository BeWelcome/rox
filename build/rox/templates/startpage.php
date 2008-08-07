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

<div class="subcolumns index_row1" style="text-align: center;">
  <div class="c33l">
    <div class="subcl">
      <div class="info">
          <h3><?php echo $words->get('IndexPageWord_share');?></h3>
          <p><a href="tour/share"><img src="images/tour/arrow_door_orange.png"></a></p>
          <p><?php echo $words->get('IndexPageWord_shareText');?></p>
      </div> <!-- info index -->
    </div> <!-- subcl -->
  </div> <!-- c33l -->

  <div class="c33l">
    <div class="subcl">
      <div class="info">
          <h3><?php echo $words->get('IndexPageWord_plan');?></h3>
          <p><a href="tour/trips"><img src="images/tour/arrow_plan_orange.png" alt="<?php echo $words->get('IndexPageWord_plan');?>"></a></p>
          <p><?php echo $words->get('IndexPageWord_planText');?></p>
      </div> <!-- info index -->
    </div> <!-- subcl -->
  </div> <!-- c33l -->
  
  <div class="c33r">
    <div class="subcr">
      <div class="info">

          <h3><?php echo $words->get('IndexPageWord_Members');?></h3>
      <!--<p><a href="tour/maps"><img src="images/tour/arrow_maps_orange.png"></a></p>
          <p><?php echo $words->get('IndexPageWord_guideText');?></p>-->
        <div class="floatbox">
        <?php require 'startpage_people.php';?>
        </div> <!-- floatbox -->
        <p style="padding-top: 5px"><?=$words->get('IndexPageWord_MembersText','<a href="searchmembers">','</a>')?></p>
      </div> <!-- info index -->
    </div> <!-- subcr -->
  </div> <!-- c33r -->
</div> <!-- subcolumns index_row1 -->
