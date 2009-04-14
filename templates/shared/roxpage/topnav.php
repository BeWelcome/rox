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

<ul>
  <li><img src="styles/css/minimal/images/icon_grey_online.png" alt="onlinemembers" /> <a href="online" id="IdLoggedMembers"><?php echo $words->getBuffered('NbMembersOnline', $who_is_online_count); ?></a><?php echo $words->flushBuffer(); ?></li>
  <?php if ($logged_in) { ?>
  <li><img src="styles/css/minimal/images/icon_grey_mail.png" alt="mymessages"/><a href="messages"><?php echo $words->getBuffered('Mymessages'); ?></a><?php echo $words->flushBuffer(); ?></li>
  <li><img src="styles/css/minimal/images/icon_grey_logout.png" alt="logout" /><a href="user/logout/<?php echo implode('/', PRequest::get()->request) ?>" id="header-logout-link"><?php echo $words->getBuffered('Logout'); ?></a><?php echo $words->flushBuffer(); ?></li>
  <?php } else { ?>
  <li><img src="styles/css/minimal/images/icon_grey_logout.png" alt="login" /><a href="<?php echo $login_url ?>#login-widget" id="header-login-link"><?php echo $words->getBuffered('Login'); ?></a><?php echo $words->flushBuffer(); ?></li>
  <li><a href="bw/signup.php"><?php echo $words->getBuffered('Signup'); ?></a><?php echo $words->flushBuffer(); ?></li>
<?php } ?>
</ul>
