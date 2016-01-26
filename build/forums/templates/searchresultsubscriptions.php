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
//	$i18n = new MOD_i18n('apps/forums/board.php');
//	$boardText = $i18n->getText('boardText');


	if ($this->BW_Right->HasRight("ForumModerator","All")) {
	   echo " As a forum moderator with right \"ForumModerator\", \"All\" you are automatically subscribed to everything in the forum.<hr />" ;
	}
?><table style="width:100%">
    <tr><td colspan="2"><h3><?= $words->get("ForumSubscriptions") ?></h3></td></tr>
    <tr><td><?= $words->getFormatted("ForumDisableAllNotifications") ?></td>
        <td style="text-align: right"><a href="forums/subscriptions/disable" class="button"><?= $words->getSilent('ForumDisable') ?></a><?= $words->flushBuffer() ?></td></tr>
    <tr><td><?= $words->getFormatted("ForumEnableAllNotifications") ?></td>
        <td style="text-align: right"><a href="forums/subscriptions/enable" class="button"><?= $words->getSilent('ForumEnable') ?></a><?= $words->flushBuffer() ?></td></tr>
    <tr><td colspan="2"><?= $words->get('ForumNotificationsInfo') ?></td></tr>
    <tr><td colspan="2"></td></tr>
    <tr><td colspan="2"><h3><?= $words->get('ForumGroupSubscriptions') ?></h3></td></tr>
<?php
if (count($TResults->Groups) > 0) {
    foreach ($TResults->Groups as $group) {
        echo '<tr><td><a href="groups/' . $group->IdGroup . '/forum">'. htmlspecialchars($group->Name) . "</a></td>";
        echo '<td style="text-align: right; text-wrap: none">';
        if ($group->AcceptMails == 'yes') {
            if ($group->notificationsEnabled) {
                echo '<a href="forums/subscriptions/disable/group/' . $group->IdGroup . '" class="button">' . $words->getSilent('ForumDisable') . '</a>' . $words->flushBuffer() . PHP_EOL;
            } else {
                echo '<a href="forums/subscriptions/enable/group/' . $group->IdGroup . '" class="button">' . $words->getSilent('ForumEnable') . '</a>' . $words->flushBuffer() . PHP_EOL;
            }
            echo ' <a href="forums/subscriptions/unsubscribe/group/' . $group->IdGroup . '" class="button">' . $words->getSilent('ForumUnsubscribe') . '</a>' . $words->flushBuffer()  . PHP_EOL;
        } else {
            echo ' <a href="forums/subscriptions/subscribe/group/' . $group->IdGroup . '" class="button">' . $words->getSilent('ForumSubscribe') . '</a>' . $words->flushBuffer()  . PHP_EOL;
        }
        echo '</td></tr>';
    }
} else {
    echo '<tr><td colspan="2">' . $words->get('ForumNoGroups') . '</td>';
}
echo '<tr><td colspan="2"><h3>' . $words->getFormatted("ForumThreadSubscriptions") . '</h3></td></tr>' . PHP_EOL;
if (count($TResults->TData) > 0) {
    foreach ($TResults->TData as $data) {
        echo '<tr>' . PHP_EOL;
        echo '<td>';
        echo '<a href="forums/s' . $data->IdThread . '">' . $words->fTrad($data->IdTitle) . '</a><br />';
        echo $data->subscribedtime;
        echo '</td>' . PHP_EOL;
        echo '<td style="text-align: right; text-wrap: none; width: 40%">';
        if ($data->notificationsEnabled > 0) {
            echo '<a href="forums/subscriptions/disable/thread/' . $data->IdThread . '/' . $data->UnSubscribeKey
                . '" class="button">' . $words->getSilent('ForumDisable') . '</a>' . $words->flushBuffer() . PHP_EOL;
        } else {
            echo '<a href="forums/subscriptions/enable/thread/' . $data->IdThread . '/' . $data->UnSubscribeKey
                . '" class="button">' . $words->getSilent('ForumEnable') . '</a>' . $words->flushBuffer() . PHP_EOL;
        }
        echo '<a href="forums/subscriptions/unsubscribe/thread/'
            . $data->IdSubscribe . '/' . $data->UnSubscribeKey . '" class="button">'
            . $words->getSilent('Unsubscribe') . '</a>' . $words->flushBuffer() . PHP_EOL;
        echo '</td>' . PHP_EOL;
        echo '</tr>' . PHP_EOL;
    }
} else {
    echo '<tr><td colspan="2">' . $words->get('ForumNoThreadsSubscribed') . '</td>';
}
echo '<tr><td><h3>' . $words->getFormatted("ForumTagSubscriptions") . '</h3></td>';
if (count($TResults->TDataTag) > 0) {
    foreach ($TResults->TDataTag as $data) {
        echo '<tr>' . PHP_EOL;
        echo '<td>';
        echo '<a href="forums/t' . $data->IdTag . '">' . $words->fTrad($data->IdName) .'</a><br />';
        echo $data->subscribedtime;
        echo '</td>' . PHP_EOL;
        echo '<td style="text-align: right; text-wrap: none: width: 40%">';
        if ($data->notificationsEnabled > 0) {
            echo '<a href="forums/subscriptions/disable/tag/' . $data->IdTag . '/' . $data->UnSubscribeKey . '"'
                . ' class="button">' . $words->getSilent('ForumDisable') . '</a>' . $words->flushBuffer() . PHP_EOL;
        } else {
            echo '<a href="forums/subscriptions/enable/tag/' . $data->IdTag . '/' . $data->UnSubscribeKey . '"'
                . ' class="button">' . $words->getSilent('ForumEnable') . '</a>' . $words->flushBuffer() . PHP_EOL;
        }
        echo '<a href="forums/subscriptions/unsubscribe/tag/' . $data->IdSubscribe . '/' . $data->UnSubscribeKey . '"'
            . ' class="button">' . $words->getSilent('Unsubscribe') . '</a>' . $words->flushBuffer() . PHP_EOL;
        echo '</td>' . PHP_EOL;
        echo '</tr>' . PHP_EOL;
    }
} else {
    echo '<tr><td colspan="2">' . $words->get('ForumNoTagsSubscribed') . '</td>';
}
?>
</table>
