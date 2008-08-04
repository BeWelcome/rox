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

<?php for ($ii=0;$ii<count($members);$ii++) {
        $m=$members[$ii] ;
?>
            <div class="float_left" style="">
                    <div style="width: 50px; float: left; background: transparent url(images/misc/userpic5050bg.gif) no-repeat top left; padding: 7px; margin-right: 10px">
                    <?php echo MOD_layoutbits::PIC_50_50($m->Username,'','') ?>
                    </div>
                <p style="float:left; text-align: left; margin-top: 10px">
                    <span class="username"><?php echo '<a href="bw/member.php?cid='.$m->Username.'">'.$m->Username.'\'s place</a>' ?></span><br />
                    <span class="small grey">in <?php echo $m->cityname; ?>, <?php echo $m->countryname; ?></span>
                </p>
            </div> <!-- float_left -->
<?php } ?>
