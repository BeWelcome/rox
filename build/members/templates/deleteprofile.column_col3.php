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
/** 
 * @author matthias (globetrotter_tt)>
 */
 
$username = $this->member->Username;
?>

<form method="post" action="retire">
    <input type="hidden" name="action" value="retire" />
    <p><?php echo $words->getFormatted ('retire_explanation',$username); ?></p>
    <h4><?php echo $words->getFormatted ('retire_membercanexplain'); ?></h4>
    <textarea cols="65" rows="6"></textarea>
    <p>
        <input type="checkbox" name="Complete_retire" 
            onclick="return confirm ('<?php echo $words->getFormatted ('retire_WarningConfirmWithdraw');?>')" />
        <?php echo $words->getFormatted ('retire_fulltickbox'); ?>
    </p>
    <p class="center">
        <input type="submit"
            onclick="return confirm ('<?php echo $words->getFormatted ('retire_WarningConfirmRetire');?>')" />
    </p>
</form>