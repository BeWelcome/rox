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
?>
<ul class="list-inline pull-right topnav">
<?php
if ($logged_in) {
echo '<li><b>' . $username . '</b></li>';
if ($R->hasRight('Comments')) {
    echo '<li><a href="bw/admin/admincomments.php" title="Review negative comments">Negative comments (' . $numberReportedComments . ')</a></li>';
}
if ($R->hasRight('Checker')) {
    echo '<li><a href="bw/admin/adminchecker.php?action=viewSpamSayMember" title="Review messages reported by users as spam">Reported messages ('.$numberSpamToBeChecked.')</a></li>';
}
if ($logged_in) {
    ?>
    <li><i class="<?php echo $envelopestyle ?>" title="<?php echo $words->getBuffered('Mymessages'); ?>"></i> <a href="messages"><?php echo $words->getBuffered('Mymessages'); ?></a> <?php echo $nbOfNewMessagees;?>
    </li>
    <li><i class="fa fa-sign-out" title="<?php echo $words->getBuffered('Logout'); ?>"></i> <a href="logout" id="header-logout-link"><?php echo $words->getBuffered('Logout'); ?></a><?php echo $words->flushBuffer(); ?></li>
<?php } else { ?>
    <li><i class="fa fa-power-off" title="<?php echo $words->getBuffered('Login'); ?>"></i> <a href="<?php echo $login_url ?>#login-widget" id="header-login-link"><?php echo $words->getBuffered('Login'); ?></a><?php echo $words->flushBuffer(); ?></li>
    <li><a href="signup"><?php echo $words->getBuffered('Signup'); ?></a><?php echo $words->flushBuffer(); ?></li>
<?php } ?>
</ul>