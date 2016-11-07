<?php
$activityInTheFuture = (time()-7*24*60*60 < strtotime($this->activity->dateTimeEnd));
$formkit = $this->layoutkit->formkit;
$callbackTagsJoinEdit = $formkit->setPostCallback('ActivitiesController', 'joinLeaveActivityCallback');
$callbackTagsCancelUncancel = $formkit->setPostCallback('ActivitiesController', 'cancelUncancelActivityCallback');

$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
$login_url = 'login/'.htmlspecialchars(implode('/', $request), ENT_QUOTES);
$purifier = MOD_htmlpure::getActivitiesHtmlPurifier();
$status = array();
if ($this->_session->has( 'ActivityStatus' )) {
    $status = $this->_session->get('ActivityStatus');
    $this->_session->remove('ActivityStatus');
}
if (!empty($status)) {
    echo '<div class="alert alert-success" role="alert"><strong>' . $words->get($status[0], $status[1]) . '</strong></div>';
}
if ($this->activity->status == 1) {
    // the activity has been cancelled
    echo '<div class="alert alert-warning" role="alert"><strong>' . $words->get('ActivityHasBeenCancelled') . '</strong></div>';
}
$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    $errStr = '<div class="alert alert-danger" role="alert"><strong>';
    foreach ($errors as $error) {
        $errStr .= $words->get($error) . "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</strong></div>';
    echo $errStr;
}
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    if ($this->member) {
        $vars['activity-comment'] = $this->member->comment;
    } else {
        $vars['activity-comment'] = '';
    }
}
?>

<div class="row mb-1">
    <div class="col-xs-12 col-md-3">

        <?
        if ($this->activity->dateStart == $this->activity->dateEnd){
            echo '<i class="fa fa-calendar"></i> '. $this->activity->dateStart .'<br>';
            echo '<i class="fa fa-clock-o"></i> <span class="compacttext back">'. $this->activity->timeStart .' - ' . $this->activity->timeEnd .'<br>';
        } else {
            echo '<i class="fa fa-calendar"></i> '. $this->activity->dateStart .' - <span class="compacttext back">'. $this->activity->timeStart. '</span><br>';
            echo '<i class="fa fa-calendar"></i> ' . $this->activity->dateEnd .' - <span class="compacttext back">'. $this->activity->timeEnd .'</span>';
        } ?>

    </div>
    <div class="col-xs-12 col-md-8">
        <h2><?php echo $this->activity->title; ?></h2>
    </div>
    <div class="col-xs-12 col-md-1 text-xs-right">
        <h4><?php $words->get('ActivityAttendeesNumbersTitle'); ?></h4>
        <?php if ($this->activity->attendeesYes != 0){ echo $words->get('ActivityAttendeesYes', $this->activity->attendeesYes) . '<br>';} ?>
        <?php if ($this->activity->attendeesMaybe != 0){ echo $words->get('ActivityAttendeesMaybe', $this->activity->attendeesMaybe) . '<br>';} ?>
        <?php if ($this->activity->attendeesNo != 0){ echo $words->get('ActivityAttendeesNo', $this->activity->attendeesNo);} ?>
    </div>
</div>

<div class="row">
    <div class="col-xs-12 col-md-3">
        <div>
            <img class="mappreview" src="https://maps.googleapis.com/maps/api/staticmap?center={{ member.Latitude }},{{ member.Longitude }}&zoom=10&size=117x117&key=AIzaSyAiF_lG8CdC-hCIXbGs9jilOFJRoXteM3k">
        </div>
        <div class="mt-1">
            <h4><?= $words->get('ActivityLocationAddress'); ?></h4>
            <?php echo $this->activity->address ?><br>
            <?php echo '<strong>' . $this->activity->location->name . '<br>' . $this->activity->location->getCountry()->name . '</strong>'; ?>
        </div>
        <div class="mt-1">
            <h4><?php echo $words->get('ActivityOrganizers');?></h4>

            <?php
            foreach ($this->activity->organizers as $organizer) { ?>
                <div>
                    <a href="members/<? echo $organizer->Username; ?>"><? echo $organizer->Username; ?></a>
                </div>
                <div class="media-body pa-0">
                    <? echo '  <span class="small">' . htmlspecialchars($organizer->comment) . '</span>'; ?>
                </div>
            <? } ?>

        </div>
    </div>
    <div class="col-xs-12 col-md-6">
        <?php echo $purifier->purify($this->activity->description); ?>
    </div>
    <div class="col-xs-12 col-md-3">
        <h4 class="card-title">Join<br></h4>
        <?php
        if ($this->member) {
            if ($this->activity->status == 0) { ?>
                <form method="post" id="activity-show-form" class="form full abitlower">
                    <?php echo $callbackTagsJoinEdit; ?>
                    <input type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />

                    <div>

                        <div class="radio">
                            <label>
                                <input value="activity-yes" id="activity-yes" name="activity-status" type="radio" <?php if ($this->member->status <= 1) { echo 'checked="checked"'; } if (!$activityInTheFuture) { echo ' disabled'; } ?>>
                                <small><?php echo $words->getSilent('ActivityYes'); ?></small>
                            </label>
                        </div>

                        <div class="radio">
                            <label>
                                <input value="activity-maybe" id="activity-maybe" name="activity-status" type="radio" <?php if ($this->member->status == 2) { echo 'checked="checked"'; } if (!$activityInTheFuture) { echo ' disabled'; } ?>>
                                <small><?php echo $words->getSilent('ActivityMaybe'); ?></small>
                            </label>
                        </div>

                        <div class="radio">
                            <label>
                                <input value="activity-no" id="activity-no" name="activity-status" type="radio" <?php if ($this->member->status == 3) { echo 'checked="checked"'; }?> <?php if ($this->member->organizer) { echo ' disabled'; } if (!$activityInTheFuture) { echo ' disabled'; } ?>>
                                <small><?php echo $words->getSilent('ActivityNo'); ?></small>
                            </label>
                        </div>

                    </div>

                    <input class="form-control mb-1" type="text" maxlength="80" id="activity-comment" name="activity-comment" value="<?php echo htmlspecialchars($vars['activity-comment'], ENT_QUOTES); ?>" placeholder="<?php echo $words->get('ActivityYourComment'); ?>" />

                    <?php
                    if ($activityInTheFuture){
                    if ($this->member->status == 0) {
                        echo '<button type="submit" id="activity-join" name="activity-join" class="btn btn-primary btn-block" title="' . $words->getSilent('ActivityJoinTheFun') . '">' . $words->getSilent('ActivityJoinTheFun') . '</button>';
                    } else {
                        echo '<button type="submit" id="activity-update" name="activity-edit" class="btn btn-primary btn-block" title="' . $words->getSilent('ActivityUpdate') . '">' . $words->getSilent('ActivityUpdate') . '</button>';

                        if (!$this->member->organizer) {
                            echo '<button type="submit" id="activity-leave" name="activity-leave" class="btn btn-primary btn-block" title="' . $words->getSilent('ActivityLeave') . '" >' . $words->getSilent('ActivityLeave') . '</button>';
                        }
                    }
                    } else {
                        echo '<button type="submit" class="btn btn-primary btn-block" disabled>Past activity</button>';
                    }
                    ?>

                </form>
                <?php
            }
        } else {
            echo '<p>'.$words->getBuffered('ActivitiesPleaseLogInToJoinActivity', '<a href="' . $login_url . '">', '</a>').'</p>';
        }
        ?>


        <?php if ($this->member) {
            if ($this->member->organizer == true) { ?>
                <form method="post" id="activity-show-form">
                <div class="form-group row pa-1">
                <span class="h4"><?php echo $words->get('ActivityOrgaStatusHeadline');?></span>
                <?php echo $callbackTagsCancelUncancel; ?>
                <input type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />
                <?php
                if (($this->activity->status == 1)&&($activityInTheFuture)) {
                    echo '<button type="submit" class="btn btn-primary btn-block" id="activity-uncancel" name="activity-uncancel">' . $words->getSilent('ActivityUnCancel') . '</button>';
                } else {
                    if ($activityInTheFuture) {
                        echo '<a href="activities/' . $this->activity->id . '/edit" role="button" class="btn btn-primary btn-block">' . $words->getSilent('ActivityEdit') . '</a>';
                    }
                    echo '<button type="submit" class="btn btn-primary btn-block" id="activity-cancel" name="activity-cancel"';
                    if (!$activityInTheFuture) { echo ' disabled'; }
                    echo '>' . $words->getSilent('ActivityCancel') . '</button>';
                }
                echo '</div></form>';
            }
        } ?>


                    <h4>Attendees</h4>

        <?php if ($this->member) { ?>

            <?php echo $this->attendeesPager->render(); ?>

            <?php
            foreach ($this->attendeesPager->getActiveSubset($this->activity->attendees) as $attendee) {
                echo '<div>';
                echo '<a href="members/' . $attendee->Username . '">' . $attendee->Username . '</a>';
                echo '</div>';
                echo '<div class="small"><strong>';
                switch ($attendee->status) {
                    case 1:
                        echo $words->get('ActivityYesIAttend');
                        break;
                    case 2:
                        echo $words->get('ActivityIMightAttend');
                        break;
                    case 3:
                        echo $words->get('ActivitySorryCantJoinYou');
                        break;
                }
                echo '</strong></div><br>';
                echo '<div class="small">' . htmlspecialchars($attendee->comment) . '</div>';
            }
            echo $this->attendeesPager->render();
            ?>

            <?php
        } else {
            echo '<div><h3>' .  $words->get('ActivityAttendees') . '</h3>';
            echo '<p>'.$words->getBuffered('ActivitiesLogInWhoIsComing', '<a href="' . $login_url . '">', '</a>').'</p>';
            echo '</div>';
        }?>
    </div>
</div>

<?php echo $words->flushBuffer(); ?>
