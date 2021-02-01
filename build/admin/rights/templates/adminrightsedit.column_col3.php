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

include 'adminrightserrors.php';

$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminRightsController', 'editCallback');
?>
<form class="yform" method="post">
    <?= $callbackTags ?>
    <input type="hidden" name="rightid" value="<?= $this->vars['right'] ?>" />
    <div class="form-group">
        <label for="username"><?php echo $this->words->get("AdminRightsUserName")?></label>
        <input type="text" class="o-input" id="username" name="username" readonly="readonly" value="<?= $this->vars['username'] ?>"/>
    </div>
    <div class="form-group">
        <label for="right"><?php echo $words->get("AdminRightsRights")?></label>
        <?= $this->rightsSelect($this->rights, $this->vars['right'], true) ?>
    </div>
    <div class="form-group">
        <label for="level"><?php echo $words->get("AdminRightsLevel") ?></label>
        <?= $this->levelSelect($this->vars['level'], false, false) ?>
    </div>
    <div class="form-group">
        <label for="scope"><?php echo $this->words->get("AdminRightsScope") ?></label>
        <input type="text" class="o-input" id="scope" name="scope" value="<?= htmlentities($this->vars['scope'], ENT_COMPAT, 'utf-8') ?>"/>
    </div>
    <div class="form-group">
        <label for="comment"><?php echo $this->words->get("AdminRightsComment") ?></label>
        <textarea class="o-input" id="comment" name="comment" rows="5"><?= htmlentities($this->vars['comment'], ENT_COMPAT, 'utf-8') ?></textarea>
    </div>
    <input type="submit" class="btn btn-primary" id="AdminRightsSubmit" name="AdminRightsSubmit" value="<?php echo $this->words->getSilent("AdminRightsSubmit")?>" /><?php echo $words->flushBuffer(); ?>
</form>
