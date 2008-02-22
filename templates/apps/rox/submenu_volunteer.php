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
      <div id="middle_nav" class="clearfix">
        <div id="nav_sub">
          <ul> 
            <li id="sub1" <?php if ($subTab=='dashboard') {echo 'class="active"';}?>>
			  <a style="cursor:pointer;" href="volunteer/dashboard">
			    <span><?php echo $words->getBuffered('VolunteerDashboard'); ?></span>
			  </a>
              <?php echo $words->flushBuffer(); ?>
			</li>
            <li id="sub2" <?php if ($subTab=='tools') {echo 'class="active"';}?>>
              <a style="cursor:pointer;" href="volunteer/tools">
                <span><?php echo $words->getBuffered('VolunteerTools'); ?></span>
              </a>
              <?php echo $words->flushBuffer(); ?>
            </li>
			<li id="sub4" <?php if ($subTab=='search') {echo 'class="active"';}?>>
              <a style="cursor:pointer;" href="volunteer/search">
                <span><?php echo $words->getBuffered('VolunteerSearch'); ?></span>
              </a>
              <?php echo $words->flushBuffer(); ?>
            </li>
			<li id="sub4" <?php if ($subTab=='tasks') {echo 'class="active"';}?>>
              <a style="cursor:pointer;" href="volunteer/tasks">
                <span><?php echo $words->getBuffered('VolunteerTasks'); ?></span>
              </a>
              <?php echo $words->flushBuffer(); ?>
            </li>
			<li id="sub4" <?php if ($subTab=='features') {echo 'class="active"';}?>>
              <a style="cursor:pointer;" href="volunteer/features">
                <span><?php echo $words->getBuffered('VolunteerFeatures'); ?></span>
              </a>
              <?php echo $words->flushBuffer(); ?>
            </li>	
          	
          </ul>
        </div>
      </div>

