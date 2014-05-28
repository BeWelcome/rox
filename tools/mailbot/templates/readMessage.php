<?php

/**
 * This template goes inside mail_html.php template used by mailbot to send out member-to-member email
 *
 * Copyright (c) 2007 BeVolunteer
 *
 * This file is part of BW Rox.
 *
 * BW Rox is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * BW Rox is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, see <http://www.gnu.org/licenses/> or•
 * write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,•
 * Boston, MA  02111-1307, USA.
 *
 * @category Tools
 * @package  Mailbot
 * @author   Laurent Savaëte (franskmanen) <laurent.savaete@gmail.com>
 * @license  http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL) v2
 * @link     http://www.bewelcome.org
 */
?>
<div id="message" class="floatbox" style="font-size: 100%">
    <div id="shade_top"> </div>
    <table id="messageheader" style="background-color: #FFF; border-bottom:1px solid #E5E5E5; border-top:1px solid #E5E5E5; padding: 2em 3em 1em;">
        <td style="color:#777">
            <?php echo $words->get('MessagesDate')?> : <?php echo date($words->getSilent('DateFormatShort'), strtotime($message->created)) ?>
        </td>
        <td style="text-align: right;">
            <a href="<?php echo $inboxUrl ?>/with/<?php echo $contact_username ?>" style="color: #2183E8; font-size: 0.9em; text-decoration: none;">
                    <?php $commentsPngUrl = $baseuri."images/icons/comments.png"; ?>
                <img src="<?php echo $commentsPngUrl ?>" alt="<?php echo $words->getSilent('messages_allmessageswith', $contact_username)?>" title="<?php echo $words->getSilent('messages_allmessageswith', $contact_username)?>" />
                <?php echo $words->getSilent('messages_allmessageswith', $contact_username)?></a>
        </td>
        </tr>
    </table>
    <div id="messagecontent" style="padding: 2em 3em;">
        <p style="color: #333; font-family: Georgia; font-size: 1.3em; font-style: italic;">
        <?php echo $purifier->purify(str_replace("\n", "<br />", $message->Message)); ?>
        </p>
    </div> <!-- messagecontent -->
    <div id="messagefooter" style="border-top: 1px solid #E5E5E5; padding: 2em 3em;">
        <p class="floatbox">
          <a style="border: 1px solid; border-color: #E5E5E5 #BBBBBB #BBBBBB #E5E5E5; color: #FF8800; float: right; font-size: 1.1em; font-weight: bold; line-height: 24px; padding: 3px 8px; text-decoration:none;" href="<?php echo $messageUrl ?>/reply"><?php echo $words->get('replymessage')?></a>
        </p>
    </div> <!-- messagefooter -->
</div> <!-- message -->
<?php  echo $words->flushBuffer()?>
