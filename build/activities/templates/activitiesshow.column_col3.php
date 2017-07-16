<?php
$activityInTheFuture = (time() - 7 * 24 * 60 * 60 < strtotime($this->activity->dateTimeEnd));
$formkit = $this->layoutkit->formkit;
$callbackTagsJoinEdit = $formkit->setPostCallback('ActivitiesController', 'joinLeaveActivityCallback');
$callbackTagsCancelUncancel = $formkit->setPostCallback('ActivitiesController', 'cancelUncancelActivityCallback');

$layoutbits = new Mod_layoutbits();
$request = PRequest::get()->request;
$login_url = 'login/' . htmlspecialchars(implode('/', $request), ENT_QUOTES);
$purifierModule = new MOD_htmlpure();
$purifier = $purifierModule->getActivitiesHtmlPurifier();
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

<div class="d-flex flex-row justify-content-start w-100">

    <div>
        <h2 class="m-0"><?php echo $this->activity->title; ?></h2>
        <div>
            <?
            if ($this->activity->dateStart == $this->activity->dateEnd) {
                echo '<i class="fa fa-calendar pr-1"></i><span class="small"> ' . $this->activity->dateStart . '</span>';
                echo '<i class="fa fa-clock-o pl-3 pr-1"></i><span class="compacttext back">' . $this->activity->timeStart . ' - ' . $this->activity->timeEnd . '</span>';
            } else {
                echo '<i class="fa fa-calendar pr-1"></i><span class="small"> ' . $this->activity->dateStart . ' - ' . $this->activity->dateEnd . '</span>';
            } ?>

        </div>
    </div>
    <div class="ml-auto">
        <div class="d-flex flex-row hidden-md-down">
            <div class="pr-2 align-self-center"><i class="fa fa-3x fa-user-circle-o"></i></div>
            <div>
                <p class="text-nowrap">
                    <?php if ($this->activity->attendeesYes != 0) {
                        echo '<span class="h4">' . $words->get('ActivityAttendeesYes', $this->activity->attendeesYes) . '</span><br>';
                    } ?>
                    <?php if ($this->activity->attendeesMaybe != 0) {
                        echo '<span class="h5">' . $words->get('ActivityAttendeesMaybe', $this->activity->attendeesMaybe) . '<span></span><br>';
                    } ?>
                    <?php if ($this->activity->attendeesNo != 0) {
                        echo '<span class="h6">' . $words->get('ActivityAttendeesNo', $this->activity->attendeesNo) . '</span>';
                    } ?>
                </p>
            </div>
        </div>
    </div>
</div>

<div class="d-flex flex-row w-100">

    <div class="d-flex flex-wrap pr-2 ">
        <div class="float-left postleftcolumn pl-2 pt-2" style="width: 135px;">

            <img class="mappreview"
                 src="https://maps.googleapis.com/maps/api/staticmap?center={{ member.Latitude }},{{ member.Longitude }}&zoom=10&size=117x117&key=AIzaSyAiF_lG8CdC-hCIXbGs9jilOFJRoXteM3k">

            <h4 class="m-0 mt-1"><?= $words->get('ActivityLocationAddress'); ?></h4>
            <p class="small">
                <?php echo $this->activity->address ?><br>
                <?php echo '<strong>' . $this->activity->location->name . '<br>' . $this->activity->location->getCountry()->name . '</strong></p>'; ?>
        </div>
        <div class="float-right pl-2">
            <?php echo $purifier->purify($this->activity->description); ?>

            <?
            if ($this->activity->dateStart == $this->activity->dateEnd) {
                echo '<i class="fa fa-calendar"></i> ' . $this->activity->dateStart . '><br>';
                echo '<i class="fa fa-clock-o"></i> <span class="compacttext back">' . $this->activity->timeStart . ' - ' . $this->activity->timeEnd . '</span><br>';
            } else {
                echo '<i class="fa fa-calendar"></i> ' . $this->activity->dateStart . ' <i class="fa fa-clock-o px-2"></i><span class="compacttext back">' . $this->activity->timeStart . '</span><br>';
                echo '<i class="fa fa-calendar"></i> ' . $this->activity->dateEnd . ' <i class="fa fa-clock-o px-2"></i><span class="compacttext back">' . $this->activity->timeEnd . '</span>';
            } ?>
        </div>
    </div>
    <div class="ml-auto">
        <div class="card p-2">
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
                                           type="radio" <?php if ($this->member->status == 3) {
                                        echo 'checked="checked"';
                                    }
                                    if (($this->member->organizer) || (!$activityInTheFuture)) {
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
            <form method="post" id="activity-show-form-admin">
                <div class="form-group mt-3">
                    <span class="h4"><?php echo $words->get('ActivityOrgaStatusHeadline'); ?></span>
                    <?php echo $callbackTagsCancelUncancel; ?>
                    <input type="hidden" name="activity-id"
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

    <div class="w-100">
        <div class="w-100 mt-3"><h4 class="mb-0"><?php echo $words->get('ActivityOrganizers'); ?></h4></div>
        <div class="d-flex flex-row">
            <?php
            foreach ($this->activity->organizers as $organizer) { ?>
                <div class="d-flex mr-2">
                    <div class="mr-2"><a href="members/<?php $organizer->Username; ?>"><img
                                    src="members/avatar/<?php echo $organizer->Username; ?>?size=50"></a></div>
                    <div><a href="members/<?php echo $organizer->Username; ?>"><?php echo $organizer->Username; ?></a></div>
                </div>
            <? } ?>
        </div>
    </div>

</div>



    <div class="row mt-3">

        <div class="col-12"><h4><?php echo $words->get('ActivityAttendees'); ?></h4></div>

        <?php if ($this->member) { ?>

            <?php echo $this->attendeesPager->render(); ?>

            <?php
            foreach ($this->attendeesPager->getActiveSubset($this->activity->attendees) as $attendee) {

                ?>
                <div class="attend m-1 p-2
<?
                switch ($attendee->status) {
                    case 1:
                        echo " attendyes";
                        break;
                    case 2:
                        echo " attendmaybe";
                        break;
                    case 3:
                        echo " attendno";
                        break;
                }
                ?>
">
                    <div class="d-flex flex-row">
                        <div class="mr-2"><a href="members/<?php echo $attendee->Username; ?>"><img
                                        src="members/avatar/<?php echo $attendee->Username; ?>?size=50"></a></div>
                        <div><a href="members/<?php echo $attendee->Username; ?>"><?php echo $attendee->Username; ?></a><br>
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
                    <? if ($attendee->comment) { ?>
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
    <!-- </div> -->
<?php echo $words->flushBuffer(); ?>
