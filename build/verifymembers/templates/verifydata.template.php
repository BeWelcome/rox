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

$verification_status = $m->verification_status;
if ($verification_status) $verification_text = $words->getSilent('verifymembers_'.$verification_status);

?>
<div class="box-bg">
<div class="box">

<div class="row">
    <table>
        <tr>
            <td><?php echo MOD_layoutbits::PIC_50_50($m->Username,'',$style='framed') ?></td>
            <td>
                <?php echo '<a href="people/'.$m->Username.'">'.$m->Username.'</a>' ?>
                <?=($verification_status) ? '<img src="images/icons/shield.png" alt="'.$verification_text.'" title="'.$verification_text.'">': ''?>
                <br />
                <?php echo $m->country; ?>
            </td>
        </tr>
    </table>
</div>

<div class="row">
    <dl class="list">
        <? var_dump($m->member_data) ?>
        <dt><?=$words->getFormatted("FullName")?></dt><dd><?=$m->member_data->FirstName?> <i><?=$m->member_data->SecondName?></i> <?=$m->member_data->LastName?></dd>
        <dt><?=$words->getFormatted("HouseNumber")?></dt><dd><?=$m->member_data->HouseNumber?></dd>
        <dt><?=$words->getFormatted("StreetName")?></dt><dd><?=$m->member_data->StreetName?></dd>
        <dt><?=$words->getFormatted("Zip")?></dt><dd><?=$m->member_data->Zip?></dd>
        <dt><?=$words->getFormatted("CityName")?></dt><dd><?=$m->City?></dd>
    </dl>
</div>

</div>
</div>

<div class="row">
    <input type="checkbox" name="NameConfirmed<?=$n?>" <?=(isset($vars['NameConfirmed'.$n])) ? 'checked="checked"' : ''?>>
    <?=$words->getFormatted("verifymembers_IdoConfirmTheName", $m->Username) ?>
</div>

<div class="row">
    <input type="checkbox" name="AddressConfirmed<?=$n?>" <?=(isset($vars['AddressConfirmed'.$n])) ? 'checked="checked"' : ''?>>
    <?=$words->getFormatted("verifymembers_IdoConfirmTheAdress", $m->Username) ?>
</div>

<div class="row">
    <label for="comment"><?=$words->getFormatted("verifymembers_Comment") ?></label>
    <textarea name="comment<?=$n?>" cols="28" rows="5" style="width:auto"><?=(isset($vars['comment'.$n])) ? $vars['comment'.$n] : ''?></textarea>
</div>

<input type="hidden" name="username<?=$n?>" value="<?=$m->Username?>">
<input type="hidden" name="idmember<?=$n?>" value="<?=$m->id?>">