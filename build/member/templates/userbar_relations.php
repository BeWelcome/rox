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

    if ($Relations != "")  {
        echo "          <h3>", $words->getFormatted("MyRelations"), "</h3>\n";
        echo "          <ul class=\"linklist\">\n"; 
    	$SpecialRelation="" ;
//special relation should be in col1 (left column) -> function ShowActions needs to be changed for this 
      $iiMax=count($Relations);
      if ($iiMax>0) { // if member has declared confirmed relation
         for ($ii=0;$ii<$iiMax;$ii++) {
         echo '<div class="floatbox">';
         echo MOD_layoutbits::PIC_30_30($Relations[$ii]->Username,'',$style='float_left');
         echo '<p class="small"><a href="bw/member.php?cid='.$Relations[$ii]->Username.'">'.$Relations[$ii]->Username.'</a><br />'.MOD_layoutbits::FindTrad($Relations[$ii]->Comment,false).'</p>';
         echo '</div>';
      	  }
      } // end if member has declared confirmed relation        
        echo "          </ul>\n";
    }


?>
