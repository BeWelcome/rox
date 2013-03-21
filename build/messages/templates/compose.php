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
$model = new MembersModel();
$member = $model->getMemberWithUsername($receiver_username);
?>
<div id="message" class="floatbox">
    <div id="shade_top"></div>
    <div id="messageheader" class="floatbox">
        <div id="messageside" class="float_right">
      <p class="small grey">
        <?=$words->get('LivesIn')?> <strong><?=$member->City?>, <?=$member->Country?></strong><br />
        <?=$words->get('Speaks')?>
        <?php
        $languages = $member->get_languages_spoken();
        if (count($languages) > 0) {
          $ii = 0;
                  $max = count($languages);
          foreach($languages as $language) {
                    $space = ($ii != $max -1) ? ', ' : '';
                        ?><strong><span title="<?=$words->getBuffered('LanguageLevel_'.$language->Level) ?>"><?php
                        // Don't flush buffer as the languages levels are better translated on the profile page
                        echo $words->get($language->WordCode);?><?=$space?></span></strong><?php
                        $ii++;
                    }
        } ?>
             </p>
             <p class="small grey">
        <a href="messages/with/<?=$receiver_username ?>"><img src="images/icons/comments.png" alt="<?=$words->getSilent('messages_allmessageswith',$receiver_username)?>" title="<?=$words->getSilent('messages_allmessageswith',$receiver_username)?>" /> <?=$words->getSilent('messages_allmessageswith',$receiver_username)?></a>
             </p>
    </div> <!-- messageside -->

        <div class="float_left">
      <?=MOD_layoutbits::PIC_50_50($receiver_username) ?>
        </div>
        <div class="float_left">
      <?if (!isset($message->DateSent)) { ?>
        <h3><?=$words->get('MessageCompose')?></h3>
      <?php } ?>
      <?if (isset($message->DateSent) && isset($this->edit)) { ?>
        <h3><?=$words->get('MessageEdit')?></h3>
      <?php } ?>
      <p class="">
        <span class="grey"><?=$words->get('MessageTo','<a href="members/'.$receiver_username.'">'.$receiver_username.'</a>') ?> </span>
      </p>
      <?if (isset($message->DateSent) && !isset($this->edit)) { ?>
      <p class="">
        <span class="grey"><?=$words->get('MessagesDate')?> : </span> <?=date($words->getSilent('DateFormatShort'),strtotime($message->DateSent)) ?>
      </p>
      <?php } ?>
        </div>
    </div> <!-- messageheader -->
    <form method="post" action="<?=$page_url ?>">
        <?=$callback_tag ?>
    <div id="messagecontent">

            <?php if ($receiver_username) { ?>
            <input type="hidden" name="receiver_id" value="<?=$receiver_id ?>"/>
            <?php } else { ?>
            <p>To: <input name="receiver_username"/></p>
            <?php } ?>

            <p>
                <textarea name="text" rows="15" cols="60" ><?=$text ?></textarea>
            </p>

          <?php

                 /* Deactivate Display of Captcha
                  * (on reenabling also uncomment the Captcha line in
                  *  the model's _createMessage function)

          if ($this->_model->CaptchaNeeded($_SESSION["IdMember"])) {
            $CaptchaValue=rand(100000,999999) ;
            echo "<p>" ;
            $_SESSION["ExpectedCaptchaValue"]=$CaptchaValue ; // Store the CaptCha for comparison
            echo $words->getFormatted("ContactCaptchaRequest",$this->_model->DisplayCaptcha($CaptchaValue))," <input type=\"text\" name=\"c_verification\" value=\"\">" ;
        //    echo $words->get("ContactCaptchaRequest"," "),$this->_model->DisplayCaptcha($CaptchaValue)," <input type=\"text\" name=\"c_verification\" value=\"\">" ;
            echo "</p>" ;
          }
                 */
          ?>

    </div> <!-- messageconent -->
    <div id="messagefooter">
        <p class="float_left">
            <input type="submit" value="<?php echo $words->getBuffered('ComposeSend');?>"/><?php echo $words->flushBuffer();?>
        </p>
    </div> <!-- messagefooter -->
    </form>
    <div id="shade"> </div>
</div> <!-- message -->




