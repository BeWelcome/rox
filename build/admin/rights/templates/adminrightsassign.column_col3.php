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

$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminRightsController', 'assignCallback');
?>
<form class="row w-100" method="post">
    <?= $callbackTags ?>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="username" class="mb-0"><?php echo $this->words->get("AdminRightsUserName")?></label>
            <input type="text" class="o-input member-autocomplete" id="username" name="username" value="<?= $this->vars['username'] ?>"
                <?= ($this->member ? 'readonly="readonly"' : '') ?>
            />
        </div>
        <div class="form-group">
            <label for="right" class="mb-0"><?php echo $words->get("AdminRightsRights")?></label>
            <?= $this->rightsSelect($this->rights, $this->vars['rightid']) ?>
        </div>
        <div class="form-group">
            <label for="level" class="mb-0"><?php echo $words->get("AdminRightsLevel") ?></label>
            <?= $this->levelSelect($this->vars['level'], false, true) ?>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label for="scope" class="mb-0"><?php echo $this->words->get("AdminRightsScope") ?></label>
            <input type="text" class="o-input" id="scope" name="scope" value="<?= htmlentities($this->vars['scope'], ENT_COMPAT, 'utf-8') ?>">
            <span class="text-muted">Enter the scope. Use ';' as delimiter and &quot; around blocks</span>
        </div>
        <div class="form-group">
            <label for="comment" class="mb-0"><?php echo $this->words->get("AdminRightsComment") ?></label>
            <textarea class="o-input" id="comment" name="comment" rows="2" placeholder="Enter a comment, so that others know why the right was assigned."><?=
            htmlentities($this->vars['comment'], ENT_COMPAT, 'utf-8') ?></textarea>
        </div>
    </div>
    <div class="col-12">
        <input type="submit" class="btn btn-primary" id="AdminRightsSubmit" name="AdminRightsSubmit" value="<?php echo $this->words->getSilent("AdminRightsSubmit")?>" /><?php echo $words->flushBuffer(); ?>
    </div>
</form>
