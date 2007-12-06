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

<div class="subcolumns index_row1">
  <div class="c50l">
    <div class="subcl">
      <div class="info index">
        <div class="floatbox"><img src="images/template/index_find.gif" alt="Find" />
          <h3><?php  echo $words->get('IndexPageWord3');?></h3>
        </div> <!-- floatbox -->
        <p><?php  echo $words->get('IndexPageWord4');?></p>
        <div class="floatbox">
          <img src="images/template/index_meet.gif" alt="Home" />
          <h3><?php echo $words->get('IndexPageWord19');?></h3>
        </div> <!-- floatbox -->
        <p><?php echo $words->get('ToChangeLanguageClickFlag'); ?></p>
        <p><?php echo $flagList; ?></p>
      </div> <!-- info index -->
    </div> <!-- subcl -->
  </div> <!-- c50l -->

  <div class="c50r">
    <div class="subcr">
      <div class="info index">
        <div class="floatbox"><img src="images/template/index_home.gif" alt="Home" />
          <h3><?php  echo $words->get('IndexPageWord9');?></h3>
        </div> <!-- floatbox -->
        <p><?php  echo $words->get('IndexPageWord10');?></p>
        <div class="floatbox"><img src="images/template/index_meet.gif" alt="Home" />
          <h3><?php  echo $words->get('IndexPageWord11');?></h3>
        </div> <!-- floatbox -->
        <p><?php  echo $words->get('IndexPageWord12');?></p>
      </div> <!-- info index -->
    </div> <!-- subcr -->
  </div> <!-- c50r -->
</div> <!-- subcolumns index_row1 -->

<!-- Next row -->

<div class="subcolumns index_row2">
  <div class="c50l">
    <div class="subcl">
      <div class="info index">
        <h3><?php  echo $words->get('IndexPageWord5');?></h3>
        <p><?php  echo $words->get('IndexPageWord6');?></p>
        <h3><?php  echo $words->get('IndexPageWord7');?></h3>
        <p><?php  echo $words->get('IndexPageWord8');?></p>
      </div> <!-- info index -->
    </div> <!-- subcl -->
  </div> <!-- c50l -->

  <div class="c50r">
    <div class="subcr">
      <div class="info index">
        <h3><?php  echo $words->get('IndexPageWord13');?></h3>
        <p><?php  echo $words->get('IndexPageWord14');?></p>
        <h3><?php  echo $words->get('IndexPageWord15');?></h3>
        <p><?php  echo $words->get('IndexPageWord16');?></p>
      </div> <!-- info index -->
    </div> <!-- subcr -->
  </div> <!-- c50r -->
</div> <!-- subcolumns index_row2 -->
