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
<div class="floatbox">
    <div id="message">
    <div id="shade_top"> </div>
    <div class="floatbox" id="messageheader">
        <p class="float_left">
        <?=MOD_layoutbits::PIC_50_50($receiver_username) ?>
        </p>
        <p class="">
          <span class="grey"><?=$words->get('MessageTo','<a href="people/'.$receiver_username.'">'.$receiver_username.'</a>') ?> </span>
        </p>
        <p class="">
          <span class="grey"><?=$words->get('MessagesDate')?> : </span> <?=date($words->getSilent('DateFormatShort'),strtotime($message->DateSent)) ?>
        </p>

    </div>
    <form method="post" action="<?=$page_url ?>">
        <?=$callback_tag ?>
    <div id="messagecontent">

            <?php if ($receiver_username) { ?>
            <input type="hidden" name="receiver_id" value="<?=$receiver_id ?>"/>
            <?php } else { ?>
            <p>To: <input name="receiver_username"/></p>
            <?php } ?>

            <p>
                <textarea name="text" rows="15" cols="70" ><?=$text ?></textarea>
            </p>

        	<?php
        	if ($this->_model->CaptchaNeeded($_SESSION["IdMember"])) {
        		$CaptchaValue=rand(100000,999999) ;
        		echo "<p>" ;
        		$_SESSION["ExpectedCaptchaValue"]=$CaptchaValue ; // Store the CaptCha for comparison
        		echo $words->getFormatted("ContactCaptchaRequest",$this->_model->DisplayCaptcha($CaptchaValue))," <input type=\"text\" name=\"c_verification\" value=\"\">" ;
        //		echo $words->get("ContactCaptchaRequest"," "),$this->_model->DisplayCaptcha($CaptchaValue)," <input type=\"text\" name=\"c_verification\" value=\"\">" ;
        		echo "</p>" ;
        	}
        	?>

    </div>
    <div id="messagefooter">
        <p>
            <input type="checkbox" name="agree_spam_policy" id="IamAwareOfSpamCheckingRules">
            <label for="IamAwareOfSpamCheckingRules"><?= $words->get('IamAwareOfSpamCheckingRules'); ?></label>
        </p>

        <p>
            <input type="checkbox" name="attach_picture" id="JoinMemberPict"<?=$attach_picture ?>/>
            <label for="JoinMemberPict"><?= $words->get('JoinMyPicture'); ?></label>
        </p>

        <p>
            <input type="submit" value="send"/>
        </p>
    </div>
    </form>
    <div id="shade"> </div>
</div>




