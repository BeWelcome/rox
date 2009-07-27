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

<form method="post" action="<?=$page_url ?>">
    <?=$callback_tag ?>
    <div class="row">
        <h3><label for="email"><?php echo $words->getFormatted("InviteEmailLabel"); ?></label></h3>
        <p><input name="email" id="email" value="<?=$email?>" size="60" /></p>
        <?php if (isset($problems['email']) && $problems['email']) echo '<p class="error">'.$problems['email'].'</p>';?>
        <span class="desc"><?php echo $words->getFormatted("InviteEmailDesc"); ?></span>
    </div>
    
    <div class="row">
        <h3><label for="name"><?php echo $words->getFormatted("InviteSubjectLabel"); ?></label></h3>
        <p><input name="subject" id="name" value="<?=$subject?>" size="60" /></p>
    </div>
    
    <div class="row">
        <h3><label for="text"><?php echo $words->getFormatted("InviteTextLabel"); ?></label></h3>
        <p><textarea name="text" id="text" rows="13" cols="60"><?=$text ?></textarea></p>
        <span class="desc"><?php echo $words->getFormatted("InviteSubjectDesc"); ?></span>
    </div>
    
    <div class="row">
        <p>
            <input type="checkbox" name="attach_picture" id="JoinMemberPict"<?=$attach_picture ?>/>
            <label for="JoinMemberPict"><?php echo $words->getFormatted("InvitePicture"); ?></label>
        </p>
    </div>
    
    <p><input type="submit" value="Send Invitation"/></p>
</form>