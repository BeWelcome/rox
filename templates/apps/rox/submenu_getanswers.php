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
            <li id="sub1" <?php if ($subTab=='about') {echo 'class="active"';}?>>
			  <a style="cursor:pointer;" href="about">
			    <span><?php echo $words->getBuffered('AboutUsSubmenu'); ?></span>
			  </a>
              <?php echo $words->flushBuffer(); ?>
			</li>
            <li id="sub3" <?php if ($subTab=='faq') {echo 'class="active"';}?>>
              <a style="cursor:pointer;" href="bw/faq.php">
                <span><?php echo $words->getBuffered('Faq'); ?></span>
              </a>
              <?php echo $words->flushBuffer(); ?>
            </li>
            <li id="sub3" <?php if ($subTab=='contactus') {echo 'class="active"';}?>>
              <a style="cursor:pointer;" href="bw/feedback.php">
                <span><?php echo $words->getBuffered('ContactUs'); ?></span>
              </a>
              <?php echo $words->flushBuffer(); ?>
            </li>
          </ul>
        </div>
      </div>

