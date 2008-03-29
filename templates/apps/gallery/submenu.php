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
            <li id="sub1" <?php echo ($subTab === 'browse') ? ' class="active"' : ''; ?>><a style="cursor:pointer;" href="gallery"><span><?php echo $words->getBuffered('GalleryAllPhotos'); ?></span></a>
            <?php echo $words->flushBuffer(); ?></li>
<?php if ($User = APP_User::login()) { ?>
            <li id="sub2" <?php echo ($subTab === 'user') ? ' class="active"' : ''; ?>><a style="cursor:pointer;" href="gallery/show/user/<?=APP_User::get()->getHandle()?>"><span><?php echo $words->getBuffered('GalleryUserPhotos'); ?></span></a>
            <?php echo $words->flushBuffer(); ?></li>
            <li id="sub3" <?php echo ($subTab === 'upload') ? ' class="active"' : ''; ?>><a style="cursor:pointer;" href="gallery/upload"><span><?php echo $words->get('GalleryUpload'); ?></span></a>
            <?php echo $words->flushBuffer(); ?></li>
<?php } ?>
          </ul>
        </div>
      </div>
     

