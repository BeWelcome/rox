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
<ul id="steps">
    <li class="<?php if ($step== 'tour') echo 'active' ?>"><div class="number">1</div><div class="desc"><a href="tour"><?php echo $words->getFormatted('tour_link')?></a></div></li>
    <li class="<?php if ($step== 'share') echo 'active' ?>"><div class="number">2</div><div class="desc"><a href="tour/share"><?php echo $words->getFormatted('tour_link_share')?></a></div></li>
    <li class="<?php if ($step== 'meet') echo 'active' ?>"><div class="number">3</div><div class="desc"><a href="tour/meet"><?php echo $words->getFormatted('tour_link_meet')?></a></div></li>
    <li class="<?php if ($step== 'trips') echo 'active' ?>"><div class="number">4</div><div class="desc"><a href="tour/trips"><?php echo $words->getFormatted('tour_link_trips')?></a></div></li>
    <li class="<?php if ($step== 'maps') echo 'active' ?>"><div class="number">5</div><div class="desc"><a href="tour/maps"><?php echo $words->getFormatted('tour_link_maps')?></a></div></li>
    <li class="<?php if ($step== 'openness') echo 'active' ?>"><div class="number">6</div><div class="desc"><a href="tour/openness"><?php echo $words->getFormatted('tour_link_openness')?></a></div></li>
    <li class="<?php if ($step== 'signup') echo 'active' ?>"><div class="number">7</div><div class="desc"><a href="signup"><span><?php echo $words->getFormatted('signup_now')?></span></a></div></li>
</ul>
