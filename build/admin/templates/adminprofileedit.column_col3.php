<?php
/*
Copyright (c) 2007-2009 BeVolunteer

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
/** 
 * @author shevek
 */

/** 
 * Tresasurer management overview template
 * 
 * @package Apps
 * @subpackage Admin
 */
$formkit = $this->layoutkit->formkit;
$callback_tag = $formkit->setPostCallback('AdminController', 'profileEditCallback');

if (isset($_SESSION['AdminProfileEditStatus'])) {
    echo '<div class="success">';
    $status = $_SESSION['AdminProfileEditStatus'];
    switch($status[0]) {
        case 'Edit':
            echo $words->get('AdminProfileEditSuccessful', $status[1], $status[2], $status[3]);
            break;  
    }
    echo '</div>';
    unset($_SESSION['AdminProfileEditStatus']);
}

$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['profile-username'] = "";
    $vars['profile-emailaddress'] = "";
}

$words = new MOD_words();
?>
<form method="post">
<fieldset><legend><?php echo $words->get('AdminProfileEdit');?></legend>
<?php echo $callback_tag; 
if (!empty($errors))
{
    echo '<div class="error">';
    foreach($errors as $error) {
        echo $words->get($error) . "<br />";
    }
    echo "</div>";
}
?>
<div class="subcolumns">
<div class="subcl">
<div class="c50l"><label for="profile-username"><?php echo $words->get('AdminProfileUsername'); ?></label><br />
<input type="text" id="profile-username" name="profile-username" value="<?php if (isset($vars['profile-username'])) { echo $vars['profile-username']; };  ?>" />
</div>
<div class="c50r"><label for="profile-emailaddress"><?php echo $words->get('AdminProfileEditEmailAddress'); ?></label><br />
<input type="text" id="profile-emailaddress" name="profile-emailaddress" value="<?php if (isset($vars['profile-emailaddress'])) { echo $vars['profile-emailaddress']; };  ?>" />
</div>
<div class="float_right"><br /><input class="button" type="submit" name="updateProfile" 
        value="<?php 
        echo $words->getBuffered('AdminProfileEditSubmit');
    ?>" /><?php echo $words->flushBuffer(); ?></div>
</fieldset>
</form>