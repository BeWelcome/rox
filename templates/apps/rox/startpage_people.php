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

  <div class="index_row2">
      <div class="info">
        <div class="floatbox" style="margin-bottom: 10px">
            <h3><?php  echo $words->get('IndexPeopleThat');?></h3>

<?php for ($ii=0;$ii<count($members);$ii++) {
        $m=$members[$ii] ;
?>
        <div class="float_left" style="padding: 12px"> 
            <p class="floatbox UserpicFloated">
                <?php echo MOD_layoutbits::PIC_50_50($m->Username,'',$style='float_left framed') ?>
                <?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'</a>' ?>
                <br />
                <?php echo $m->countryname; ?>
            </p> 
        </div>
<?php } ?>
            
        </div> <!-- floatbox -->
        <p><?=$words->get('IndexPeopleThat_ManyMore','<a href="searchmembers">','</a>')?></p>
      </div> <!-- info index -->
  </div>