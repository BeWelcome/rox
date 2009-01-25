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
<div id="middle_nav" class="clearfix" >
  <div id="nav_sub">
    <ul> 
      <li id="sub1" <?php if ($subTab=='places') {echo 'class="active"';}?>>
        <a href="places/">
          <span><?php echo $words->getBuffered('Overview'); ?></span>
        </a>
        <?php echo $words->flushBuffer(); ?>
      </li>

      <li id="sub2" <?php if ($subTab=='members') {echo 'class="active"';}?>>
        <a href="places/members">
          <span><?php echo $words->getBuffered('members'); ?></span>
        </a>
        <?php echo $words->flushBuffer(); ?>
      </li>
      <li id="sub3" <?php if ($subTab=='forum') {echo 'class="active"';}?>>
        <a href="places/forum">
          <span><?php echo $words->getBuffered('forum'); ?></span>
        </a>
        <?php echo $words->flushBuffer(); ?>
      </li>
      <li id="sub4" <?php if ($subTab=='wiki') {echo 'class="active"';}?>>
        <a href="places/wiki">
          <span><?php echo $words->getBuffered('Wiki'); ?></span>
        </a>
        <?php echo $words->flushBuffer(); ?>
      </li>

    
    <!--<li id="sub4"><a style="cursor:pointer;" onClick="$('FindPeopleFilter').toggle(); $('sub4').addClassName('active'); $('sub4').siblings().each(Element.removeClassName('active'));"><span>another option</span></a></li> -->
    </ul>
  </div>
</div>

