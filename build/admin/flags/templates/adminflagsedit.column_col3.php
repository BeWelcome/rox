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

include 'adminflagserrors.php';

$callbackTags = $this->layoutkit->formkit->setPostCallback('AdminFlagsController', 'editCallback');
?>
<form class="yform" method="post">
    <?= $callbackTags ?>
    <input type="hidden" name="flagid" value="<?= $this->vars['flag'] ?>"/>
    <input type="hidden" name="level" value=""1" />
    <input type="hidden" name="scope" value=""/>

    <div class="type-text">
        <label for="username"><?php echo $this->words->get("AdminFlagsUserName")?></label>
        <input type="text" id="username" name="username" readonly="readonly" value="<?= $this->vars['username'] ?>"/>
    </div>
    <div class="type-select">
        <label for="flag"><?php echo $words->get("AdminFlagsFlags")?></label>
        <?= $this->flagsSelect($this->flags, $this->vars['flag'], true) ?>
    </div>
    <div class="type-text">
        <label for="comment"><?php echo $this->words->get("AdminFlagsComment") ?></label>
        <textarea id="comment" name="comment" rows="5"><?= htmlentities($this->vars['comment'], ENT_COMPAT, 'utf-8') ?></textarea>
    </div>
    <div class="type-button">
        <input type="submit" id="AdminFlagsSubmit" name="AdminFlagsSubmit" value="<?php echo $this->words->getSilent("AdminFlagsSubmit")?>" /><?php echo $words->flushBuffer(); ?>
    </div>
</form>