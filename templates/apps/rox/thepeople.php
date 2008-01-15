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
	
<h3><?php echo $words->get("ThePeople_Title3") ?></h3>
<p><?php echo $words->get("ThePeople_Text3")?></p>

	 