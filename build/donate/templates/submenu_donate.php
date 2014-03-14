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
          <ul class="nav nav-pills nav-justifiedmedia submenu"> 
            <li id="sub1" class="<?php echo ($sub!=='list' ? 'active-bw' : 'disabled');?>">
            <a href="donate">
            <span><?php echo $words->getBuffered('DonateLink'); ?></span></a></li>
            <li id="sub2" class="<?php echo ($sub=='list' ? 'active-bw' : 'disabled');?>"><a href="donate/list"><span><?php echo $words->getBuffered('DonateList'); ?></span></a></li>
                <?php echo $words->flushBuffer() ?>            
            <!--<li id="sub4"><a style="cursor:pointer;" onClick="$('FindPeopleFilter').toggle(); $('sub4').addClassName('active'); $('sub4').siblings().each(Element.removeClassName('active');"><span>another option</span></a></li> -->
          </ul>
