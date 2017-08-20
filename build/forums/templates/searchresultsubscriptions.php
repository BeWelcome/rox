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
	   echo '<div class="col-12 mb-3">Because of your rights you are automatically subscribed to everything in the forum.</div>>';
	}
?>

<div class="col-12"><h3><?= $words->get("ForumSubscriptions") ?></h3></div>
<div class="col-12 col-md-4"><a href="forums/subscriptions/disable" class="btn btn-primary btn-block mb-1"><?= $words->getSilent('ForumDisable') ?></a><?= $words->flushBuffer() ?></div>
<div class="col-12 col-md-8 pt-2"><?= $words->getFormatted("ForumDisableAllNotifications") ?></div>
<div class="col-12 col-md-4"><a href="forums/subscriptions/enable" class="btn btn-primary btn-block"><?= $words->getSilent('ForumEnable') ?></a><?= $words->flushBuffer() ?></div>
<div class="col-12 col-md-8 pt-2"><?= $words->getFormatted("ForumEnableAllNotifications") ?></div>

<div class="col-12 mt-3"><h3><?= $words->get('ForumGroupSubscriptions') ?></h3></div>
<?php
if (count($TResults->Groups) > 0) {
    foreach ($TResults->Groups as $group) {
        if ($group->AcceptMails == 'yes') {
            if ($group->notificationsEnabled) {
                echo '<div class="col-3"><a href="forums/subscriptions/disable/group/' . $group->IdGroup . '" class="btn btn-primary btn-block mb-1">' . $words->getSilent('ForumDisable') . '</a></div>' . $words->flushBuffer() . PHP_EOL;
            } else {
                echo '<div class="col-3"><a href="forums/subscriptions/enable/group/' . $group->IdGroup . '" class="btn btn-primary btn-block mb-1">' . $words->getSilent('ForumEnable') . '</a></div>' . $words->flushBuffer() . PHP_EOL;
            }
            echo '<div class="col-3"><a href="forums/subscriptions/unsubscribe/group/' . $group->IdGroup . '" class="btn btn-primary btn-block mb-1">' . $words->getSilent('ForumUnsubscribe') . '</a></div>' . $words->flushBuffer()  . PHP_EOL;
        } else {
            echo '<div class="col-3"></div><div class="col-3"><a href="forums/subscriptions/subscribe/group/' . $group->IdGroup . '" class="btn btn-primary btn-block mb-1">' . $words->getSilent('ForumSubscribe') . '</a></div>' . $words->flushBuffer()  . PHP_EOL;
        }
        echo '<div class="col-6 pt-2"><a href="groups/' . $group->IdGroup . '/forum">'. htmlspecialchars($group->Name) . "</a></div>";
    }
} else {
    echo '<div class="col-12">' . $words->get('ForumNoGroups') . '</div>';
}
?>

<div class="col-12 mt-3"><h3><?= $words->getFormatted("ForumThreadSubscriptions") ?></h3></div>

<?
if (count($TResults->TData) > 0) {
    foreach ($TResults->TData as $data) {
        echo '<div class="col-3">';
        if ($data->notificationsEnabled > 0) {
            echo '<a href="forums/subscriptions/disable/thread/' . $data->IdThread . '/' . $data->UnSubscribeKey
                . '" class="btn btn-primary btn-block">' . $words->getSilent('ForumDisable') . '</a></div>' . $words->flushBuffer() . PHP_EOL;
        } else {
            echo '<a href="forums/subscriptions/enable/thread/' . $data->IdThread . '/' . $data->UnSubscribeKey
                . '" class="btn btn-primary btn-block">' . $words->getSilent('ForumEnable') . '</a></div>' . $words->flushBuffer() . PHP_EOL;
        }
        echo '<div class="col-3"><a href="forums/subscriptions/unsubscribe/thread/'
            . $data->IdSubscribe . '/' . $data->UnSubscribeKey . '" class="btn btn-primary btn-block">'
            . $words->getSilent('Unsubscribe') . '</a></div>' . $words->flushBuffer() . PHP_EOL;

        echo '<div class="col-6"><a href="forums/s' . $data->IdThread . '">' . $words->fTrad($data->IdTitle) . '</a><br>';
        echo '<span class="small">' . $data->subscribedtime . "</span></div>";
    }
} else {
    echo '<div class="col-12">' . $words->get('ForumNoThreadsSubscribed') . '</div>';
}
?>
<div class="col-12 mt-3"><h3><?= $words->getFormatted("ForumTagSubscriptions") ?></h3></div>

<?
if (count($TResults->TDataTag) > 0) {
    foreach ($TResults->TDataTag as $data) {
        echo '<div class="col-3">';
        if ($data->notificationsEnabled > 0) {
            echo '<a href="forums/subscriptions/disable/tag/' . $data->IdTag . '/' . $data->UnSubscribeKey . '"'
                . ' class="btn btn-primary btn-block">' . $words->getSilent('ForumDisable') . '</a></div>' . $words->flushBuffer() . PHP_EOL;
        } else {
            echo '<a href="forums/subscriptions/enable/tag/' . $data->IdTag . '/' . $data->UnSubscribeKey . '"'
                . ' class="btn btn-primary btn-block">' . $words->getSilent('ForumEnable') . '</a></div>' . $words->flushBuffer() . PHP_EOL;
        }
        echo '<div class="col-3"><a href="forums/subscriptions/unsubscribe/tag/' . $data->IdSubscribe . '/' . $data->UnSubscribeKey . '"'
            . ' class="btn btn-primary btn-block">' . $words->getSilent('Unsubscribe') . '</a></div>' . $words->flushBuffer() . PHP_EOL;

        echo '<div class="col-6"><a href="forums/t' . $data->IdTag . '">' . $words->fTrad($data->IdName) .'</a><br />';
        echo '<span class="small">' . $data->subscribedtime . '</span></div>';
    }
} else {
    echo '<div class="col-12">' . $words->get('ForumNoTagsSubscribed') . '</div>';
}
?>
