<?php
/*

Copyflag (c) 2007-2014 BeVolunteer

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
$vars = $this->getRedirectedMem('vars');
if ($vars) {
    // overwrite the vars
    $this->vars = $vars;
}

// \todo: Handle errors

$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminSubscriptionsController', 'manageCallback');
?>
<form class="yform" method="post">
    <?= $callbackTags ?>
    <div class="type-text">
        <label for="username"><?php echo $this->words->get("AdminRightsUserName")?></label>
        <input type="text" id="username" name="username" value="<?= $this->vars['username'] ?>" />
    </div>
    <div class="type-button">
        <input type="submit" name="AdminSubscriptionsEnable" value="<?php echo $this->words->getSilent("AdminSubscriptionsEnable")?>" /><?php echo $words->flushBuffer(); ?>
        <input type="submit" name="AdminSubscriptionsDisable" value="<?php echo $this->words->getSilent("AdminSubscriptionsDisable")?>" /><?php echo $words->flushBuffer(); ?>
    </div>
</form>