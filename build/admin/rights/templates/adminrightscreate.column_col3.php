<?php
/*

Copyright (c) 2007-2014 BeVolunteer

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
$errors = $this->getRedirectedMem('errors');
if ($errors) {
    echo '<div class="error">';
    foreach($errors as $error) {
        echo '<p>' . $this->words->get($error) . '<p>';
    }
    echo '</div>';
}
$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminRightsController', 'createCallback');
?>
<form class="yform" method="post">
    <?= $callbackTags ?>
    <div class="type-text">
        <label for="name"><?php echo $this->words->get("AdminRightsName")?></label>
        <input type="text" id="name" name="name" value="<?= htmlentities($this->vars['name'], ENT_COMPAT, 'utf-8') ?>"/>
    </div>
    <div class="type-text">
        <label for="description"><?php echo $this->words->get("AdminRightsDescription") ?></label>
        <textarea id="description" name="description" rows="5"><?= htmlentities($this->vars['description'], ENT_COMPAT, 'utf-8') ?></textarea>
    </div>
    <div class="type-button">
        <input type="submit" id="AdminRightsSubmit" name="AdminRightsSubmit" value="<?php echo $this->words->getSilent("AdminRightsCreate")?>" /><?php echo $words->flushBuffer(); ?>
    </div>
</form>