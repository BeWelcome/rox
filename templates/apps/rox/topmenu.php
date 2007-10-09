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

<!-- #nav: main navigation -->
<div id="nav">
  <div id="nav_main">
    <ul>
      <li><a href="bw/main.php"><span>Home</span></a></li>
      <?php if (APP_User::isBWLoggedIn()) { ?>
      <li><a href="bw/member.php?cid=<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>"><span>My Account</span></a></li>
      <?php } ?>
      <li<?php echo ($currentTab === 'searchmembers') ? ' class="active"' : ''; ?>><a href="searchmembers/index"><span>Find Members</span></a></li>
      <li<?php echo ($currentTab === 'forums') ? ' class="active"' : ''; ?>><a href="forums"><span>Forum</span></a></li>
      <li><a href="bw/groups.php"><span>Groups</span></a></li>
      <li><a href="bw/aboutus.php"><span>Get Answers</span></a></li>
    </ul>
    
      <!-- #nav_flowright: This part of the main navigation floats to the right. The items have to be listed in reversed order to float properly-->			
    <div id="nav_flowright">
      <form action="searchmembers/quicksearch" method="post" id="form-quicksearch">
        <input type="text" name="searchtext" size="15" maxlength="30" id="text-field" value="Search..." onfocus="this.value=''";/>
        <input type="hidden" name="<?=$callbackId;?>" value="1"/>
        <input type="image" src="styles/YAML/images/icon_go.gif" id="submit-button" />
      </form>
    </div> <!-- nav_flowright -->
      
  </div>
</div>
<!-- #nav: - end -->

<!-- <div id="nav_sub">
    <ul>
        <li class="active"><a href="http://www.bewelcome.org/main.php"><span><?php echo $words->get('Menu'); ?></span></a></li>
		<li><a href="blog"><span><?php echo $words->get('Blogs'); ?></span></a></li>
        <li><a href="trip"><span>Trips<?php // FIXME: echo $words->get('Trips'); ?></span></a></li>
        <li><a href="gallery/show"><span><?php echo $words->get('Gallery'); ?></span></a></li>
        <li><a href="forums"><span><?php echo $words->get('Forum'); ?></span></a></li>
        <li><a href="wiki"><span>Wiki<?php // FIXME: echo $words->get('Wiki'); ?></span></a></li>
        <li><a href="chat"><span>Chat<?php // FIXME: echo $words->get('Chat'); ?></span></a></li>
    </ul>
</div>-->



<!--
<div id="middle_nav" class="clearfix">
	<div id="nav_sub" class="notabs">
		<ul>
		</ul>
	</div>
</div>
-->
