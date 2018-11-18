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

<div class="col-6 col-md-3 offcanvas-collapse" id="sidebar">
    <div class="list-group">
        <a class="list-group-item nav-link active" href="donate"><?php echo $words->getBuffered('DonateLink'); ?></a>
        <a class="list-group-item nav-link" href="donate/list"><?php echo $words->getBuffered('DonateList'); ?></a>
    </div>

    <h4 class="mt-3"><?php echo $words->get('Donate_FurtherInfo'); ?></h4>
    <?php echo $words->get('Donate_FurtherInfoText');?>
</div>