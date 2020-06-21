<div class="row">
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
	   echo '<div class="col-12 mb-3">Because of your rights you are automatically subscribed to everything in the forum.</div>';
	}
?>

<div class="col-12"><h3><?= $words->get("ForumSubscriptions") ?></h3></div>
<div class="col-12 col-md-4"><a href="forums/subscriptions/disable" class="btn btn-primary btn-block mb-1"><?= $words->getSilent('ForumDisable') ?></a><?= $words->flushBuffer() ?></div>
<div class="col-12 col-md-8 pt-2"><?= $words->getFormatted("ForumDisableAllNotifications") ?></div>
<div class="col-12 col-md-4"><a href="forums/subscriptions/enable" class="btn btn-primary btn-block"><?= $words->getSilent('ForumEnable') ?></a><?= $words->flushBuffer() ?></div>
<div class="col-12 col-md-8 pt-2"><?= $words->getFormatted("ForumEnableAllNotifications") ?></div>

<div class="col-12 mt-3"><h3><?= $words->get('ForumGroupSubscriptions') ?></h3></div><?= $words->flushBuffer() ?>

        <?php
        if (count($TResults->Groups) > 0) { ?>
            <div class="col-12">
            <table class="table table-hover">
            <thead>
            <tr>
                <th></th>
                <th class="w-100"><?= $words->get('TableSubscriptionsGroupName') ?></th>
                <th><?= $words->get('TableTitleSubscriptions') ?></th>
            </tr>
            </thead>
            <tbody>
        <?php
            foreach ($TResults->Groups as $group) {
        ?>
        <tr>
            <th scope="row"><img src="group/thumbimg/<?php echo $group->IdGroup; ?>" width="50" height="50"></th>
            <td class="align-middle"><a href="group/<?php echo $group->IdGroup; ?>/forum"><?php echo htmlspecialchars($group->Name); ?></a></td>
            <td class="align-middle">
                <div class="btn-group" role="group" aria-label="<?= $words->get('AriaLabelToggleSubscriptionOnOff') ?>">
                <?php if ($group->AcceptMails == 'yes') { ?>
                    <a class="btn btn-primary" style="color: #fff; cursor: default; border: 1px solid #868e96 !important;"><?= $words->get('ToggleSubscriptionOn') ?></a>
                    <a href="forums/subscriptions/unsubscribe/group/<?php echo $group->IdGroup; ?>" type="button" class="btn btn-light mb-0 border-0" style="border: 1px solid #868e96 !important;"><?= $words->get('ToggleSubscriptionOff') ?></a>
                </div></td>
                <?php
                } else { ?>
                <a href="forums/subscriptions/subscribe/group/<?php echo $group->IdGroup; ?>" type="button" class="btn btn-light mb-0 border-0" style="border: 1px solid #868e96 !important;"><?= $words->get('ToggleSubscriptionOn') ?></a>
                <a class="btn btn-primary" style="color: #fff; cursor: default; border: 1px solid #868e96 !important;"><?= $words->get('ToggleSubscriptionOff') ?></a>
                </div></td>
                <?php } ?>
        </tr>
    <?php } ?>
    </tbody>
    </table>
    </div>
<?php
} else {
    echo '<div class="col-12">' . $words->get('ForumNoGroups') . '</div>';
}
?>

<div class="col-12 mt-3"><h3><?= $words->getFormatted("ForumThreadSubscriptions") ?></h3></div>

<?php
if (count($TResults->TData) > 0) { ?>

    <div class="col-12">
        <table class="table table-hover">
            <thead>
            <tr>
                <th></th>
                <th class="w-100"><?= $words->get('TableSubscriptionsGroupName') ?></th>
                <th><?= $words->get('TableTitleSubscriptions') ?></th>
            </tr>
            </thead>
            <tbody>
<?php
    foreach ($TResults->TData as $data) {

        echo '<tr><th scope="row">';
        echo '<a href="forums/subscriptions/unsubscribe/thread/' . $data->IdSubscribe . '/' . $data->UnSubscribeKey . '" class="btn btn-danger"><i class="fa fa-times" aria-hidden="true" title="' . $words->get('SubscriptionTitleUnscribeDiscussion') . '"></i></a>';
        echo '</th>';
        echo '<td class="align-middle"><a href="forums/s' . $data->IdThread . ' ">' . $words->fTrad($data->IdTitle) . '</a><br>';
        echo '<span class="small">' . $data->subscribedtime . '</span></td>';

        echo '<td class="align-middle"><div class="btn-group" role="group" aria-label="' . $words->get('AriaLabelToggleSubscriptionOnOff') . '">';

        if ($data->notificationsEnabled > 0) {
            // on - turn off
            echo '<a class="btn btn-primary" style="color: #fff; cursor: default; border: 1px solid #868e96 !important;">' . $words->get('ToggleSubscriptionOn') . '</a>';
            echo '<a href="forums/subscriptions/disable/thread/' . $data->IdThread . '/' . $data->UnSubscribeKey . '" type="button" class="btn btn-light mb-0 border-0" style="border: 1px solid #868e96 !important;">' . $words->get('ToggleSubscriptionOff') . '</a>';
        } else {
            // off - turn on
            echo '<a href="forums/subscriptions/enable/thread/' . $data->IdThread . '/' . $data->UnSubscribeKey . '"  type="button" class="btn btn-light mb-0 border-0" style="border: 1px solid #868e96 !important;">' . $words->get('ToggleSubscriptionOn') . '</a>';
            echo '<a class="btn btn-primary" style="color: #fff; cursor: default; border: 1px solid #868e96 !important;">' . $words->get('ToggleSubscriptionOff') . '</a>';
        }

        echo '</td></tr>';


    }
    echo '</tbody></table></div>';
} else {
    echo '<div class="col-12">' . $words->get('ForumNoThreadsSubscribed') . '</div>';
}
?>
</div>
