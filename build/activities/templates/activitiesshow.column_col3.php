<?php
$activityInTheFuture = (time() - 7 * 24 * 60 * 60 < strtotime($this->activity->dateTimeEnd));
$formkit = $this->layoutkit->formkit;
$callbackTagsJoinEdit = $formkit->setPostCallback('ActivitiesController', 'joinLeaveActivityCallback');
$callbackTagsCancelUncancel = $formkit->setPostCallback('ActivitiesController', 'cancelUncancelActivityCallback');

$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
$login_url = 'login/' . htmlspecialchars(implode('/', $request), ENT_QUOTES);
$purifier = MOD_htmlpure::getActivitiesHtmlPurifier();
$status = array();
if ($this->_session->has('ActivityStatus')) {
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
    <div class="col-12 col-md-2">

        <?
        if ($this->activity->dateStart == $this->activity->dateEnd) {
            echo '<i class="fa fa-calendar"></i> ' . $this->activity->dateStart . '<br>';
            echo '<i class="fa fa-clock-o"></i> <span class="compacttext back">' . $this->activity->timeStart . ' - ' . $this->activity->timeEnd . '<br>';
        } else {
            echo '<i class="fa fa-calendar"></i> ' . $this->activity->dateStart . ' - <span class="compacttext back">' . $this->activity->timeStart . '</span><br>';
            echo '<i class="fa fa-calendar"></i> ' . $this->activity->dateEnd . ' - <span class="compacttext back">' . $this->activity->timeEnd . '</span>';
        } ?>

    </div>
    <div class="col-12 col-md-7">
        <h2><?php echo $this->activity->title; ?></h2>
    </div>
    <div class="col-12 col-md-3">
        <h4><?php $words->get('ActivityAttendeesNumbersTitle'); ?></h4>
        <div class="d-flex flex-row hidden-md-down">
            <div class="d-flex align-items-center pr-2"><i class="fa fa-3x fa fa-user-circle-o"></i></div>
            <div>
                <small>
                    <?php if ($this->activity->attendeesYes != 0) {
                        echo $words->get('ActivityAttendeesYes', $this->activity->attendeesYes) . '<br>';
                    } ?>
                    <?php if ($this->activity->attendeesMaybe != 0) {
                        echo $words->get('ActivityAttendeesMaybe', $this->activity->attendeesMaybe) . '<br>';
                    } ?>
                    <?php if ($this->activity->attendeesNo != 0) {
                        echo $words->get('ActivityAttendeesNo', $this->activity->attendeesNo);
                    } ?>
                </small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-2">
        <div>
            <img class="mappreview"
                 src="https://maps.googleapis.com/maps/api/staticmap?center={{ member.Latitude }},{{ member.Longitude }}&zoom=10&size=117x117&key=AIzaSyAiF_lG8CdC-hCIXbGs9jilOFJRoXteM3k">
        </div>
        <div class="mt-1">
            <h4><?= $words->get('ActivityLocationAddress'); ?></h4>
            <?php echo $this->activity->address ?><br>
            <?php echo '<strong>' . $this->activity->location->name . '<br>' . $this->activity->location->getCountry()->name . '</strong>'; ?>
        </div>
    </div>
    <div class="col-12 col-md-7">

            <?php echo $purifier->purify($this->activity->description); ?>

    </div>
    <div class="col-12 col-md-3">
        <div class="card p-2 mt-3">
            <h4 class="card-title">Join<br></h4>
            <?php
            if ($this->member) {
                if ($this->activity->status == 0) { ?>
                    <form method="post" id="activity-show-form" class="form full abitlower">
                        <?php echo $callbackTagsJoinEdit; ?>
                        <input type="hidden" id="activity-id" name="activity-id"
                               value="<?php echo $this->activity->id; ?>"/>

                        <div>
                            <div class="radio">
                                <label>
                                    <input value="activity-yes" id="activity-yes" name="activity-status"
                                           type="radio" <?php if ($this->member->status <= 1) {
                                        echo 'checked="checked"';
                                    }
                                    if (!$activityInTheFuture) {
                                        echo ' disabled';
                                    } ?>>
                                    <small><?php echo $words->getSilent('ActivityYes'); ?></small>
                                </label>
                            </div>

                            <div class="radio">
                                <label>
                                    <input value="activity-maybe" id="activity-maybe" name="activity-status"
                                           type="radio" <?php if ($this->member->status == 2) {
                                        echo 'checked="checked"';
                                    }
                                    if (!$activityInTheFuture) {
                                        echo ' disabled';
                                    } ?>>
                                    <small><?php echo $words->getSilent('ActivityMaybe'); ?></small>
                                </label>
                            </div>

                            <div class="radio">
                                <label>
                                    <input value="activity-no" id="activity-no" name="activity-status"
                                           type="radio" <?php if ($this->member->status == 3) { echo 'checked="checked"'; }
                                    if (($this->member->organizer)||(!$activityInTheFuture)) {
                                        echo ' disabled';
                                    }
                                    ?>
                                    >
                                    <small><?php echo $words->getSilent('ActivityNo'); ?></small>
                                </label>
                            </div>
                        </div>

                        <input class="form-control mb-1" type="text" maxlength="80" id="activity-comment"
                               name="activity-comment"
                               value="<?php echo htmlspecialchars($vars['activity-comment'], ENT_QUOTES); ?>"
                               placeholder="<?php echo $words->get('ActivityYourComment'); ?>"/>

                        <?php
                        if ($activityInTheFuture) {
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
                echo '<p>' . $words->getBuffered('ActivitiesPleaseLogInToJoinActivity', '<a href="' . $login_url . '">', '</a>') . '</p>';
            }
            ?>


            <?php if ($this->member) {
            if ($this->member->organizer == true) { ?>
            <form method="post" id="activity-show-form">
                <div class="form-group row pa-1">
                    <span class="h4"><?php echo $words->get('ActivityOrgaStatusHeadline'); ?></span>
                    <?php echo $callbackTagsCancelUncancel; ?>
                    <input type="hidden" id="activity-id" name="activity-id"
                           value="<?php echo $this->activity->id; ?>"/>
                    <?php
                    if (($this->activity->status == 1) && ($activityInTheFuture)) {
                        echo '<button type="submit" class="btn btn-primary btn-block" id="activity-uncancel" name="activity-uncancel">' . $words->getSilent('ActivityUnCancel') . '</button>';
                    } else {
                        if ($activityInTheFuture) {
                            echo '<a href="activities/' . $this->activity->id . '/edit" role="button" class="btn btn-primary btn-block">' . $words->getSilent('ActivityEdit') . '</a>';
                        }
                        echo '<button type="submit" class="btn btn-primary btn-block" id="activity-cancel" name="activity-cancel"';
                        if (!$activityInTheFuture) {
                            echo ' disabled';
                        }
                        echo '>' . $words->getSilent('ActivityCancel') . '</button>';
                    }
                    echo '</div></form>';
                    }
                    } ?>
                </div>
        </div>
    </div>
</div>
<div class="row mt-3">


        <div class="col-12"><h4 class="mb-0"><?php echo $words->get('ActivityOrganizers'); ?></h4></div>

        <?php
        foreach ($this->activity->organizers as $organizer) { ?>
            <div class="d-flex flex-row m-2 p-2" style="border: 1px dashed #ccc;">
                <div class="mr-2"><a href="members/<?php $organizer->Username; ?>"><img src="members/avatar/<?php echo $organizer->Username; ?>?size=50"></a></div>
                <div><a href="members/<?php $organizer->Username; ?>"><?php echo $organizer->Username; ?></a></div>
            </div>
        <? } ?>


    <div class="col-12"><h4><?php echo $words->get('ActivityAttendees'); ?></h4></div>

        <?php if ($this->member) { ?>

            <?php echo $this->attendeesPager->render(); ?>

            <?php
            foreach ($this->attendeesPager->getActiveSubset($this->activity->attendees) as $attendee) {

                ?>
                <div class="col-auto m-1 p-2
<?
                switch ($attendee->status) {
                    case 1:
                        echo "attendyes";
                        break;
                    case 2:
                        echo "attendmaybe";
                        break;
                    case 3:
                        echo "attendno";
                        break;
                }
                ?>
">
                    <div class="d-flex flex-row">
                        <div class="mr-2"><a href="members/<?php $attendee->Username; ?>"><img src="members/avatar/<?php echo $attendee->Username; ?>?size=50"></a></div>
                        <div><a href="members/<?php $attendee->Username; ?>"><?php echo $attendee->Username; ?></a><br>
                            <small>
                                <?
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
                                ?>
                            </small>
                        </div>
                    </div>
                    <? if ($attendee->comment){ ?>
                    <div class="small gray"><i><?php echo htmlspecialchars($attendee->comment); ?></i></div>
                    <? } ?>
                </div>
                <?php
            }
            echo $this->attendeesPager->render();
        } else {
            echo '<div><h3>' . $words->get('ActivityAttendees') . '</h3>';
            echo '<p>' . $words->getBuffered('ActivitiesLogInWhoIsComing', '<a href="' . $login_url . '">', '</a>') . '</p></div>';
        } ?>

</div>
<?php echo $words->flushBuffer(); ?>
