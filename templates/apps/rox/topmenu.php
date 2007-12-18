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

<!-- #nav: main navigation -->
<div id="nav">
  <div id="nav_main">
    <ul>
      <?php if (APP_User::isBWLoggedIn()) { ?>
         <li<?php echo ($currentTab === 'main') ? ' class="active"' : ''; ?>><?php echo $words->prepare('Menu'); ?><a href="main"><span><?php echo $words->getSilent('Menu'); ?></span></a></li>
        <li><?php echo $words->prepare('MyProfile'); ?><a href="bw/member.php?cid=<?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?>"><span><?php echo $words->getSilent('MyProfile'); ?></span></a></li>
      <?php } ?>
      <li<?php echo ($currentTab === 'searchmembers') ? ' class="active"' : ''; ?>><?php echo $words->prepare('FindMembers'); ?><a href="searchmembers/index"><span><?php echo $words->getSilent('FindMembers'); ?></span></a></li>
      <li<?php echo ($currentTab === 'forums') ? ' class="active"' : ''; ?>><?php echo $words->prepare('Community'); ?><a href="forums"><span><?php echo $words->getSilent('Community'); ?></span></a></li>
      <li><?php echo $words->prepare('Groups'); ?><a href="bw/groups.php"><span><?php echo $words->getSilent('Groups'); ?></span></a></li>
      <li<?php echo ($currentTab === 'gallery') ? ' class="active"' : ''; ?>><?php echo $words->prepare('Gallery'); ?><a href="gallery"><span><?php echo $words->getSilent('Gallery'); ?></span></a></li>
      <li><?php echo $words->prepare('GetAnswers'); ?><a href="about"><span><?php echo $words->getSilent('GetAnswers'); ?></span></a></li>
    </ul>
    
      <!-- #nav_flowright: This part of the main navigation floats to the right. The items have to be listed in reversed order to float properly-->			
    <div id="nav_flowright">
      <form action="searchmembers/quicksearch" method="post" id="form-quicksearch">
        <input type="text" name="searchtext" size="15" maxlength="30" id="text-field" value="Search...." onfocus="this.value='';"/>
        <?php PPostHandler::setCallback('quicksearch_callbackId', 'SearchmembersController', 'index'); ?>
        <input type="hidden" name="quicksearch_callbackId" value="1"/>
        <input type="image" src="styles/YAML/images/icon_go.gif" id="submit-button" />
      </form>
    </div> <!-- nav_flowright -->
      
  </div>
</div>
<!-- #nav: - end -->

