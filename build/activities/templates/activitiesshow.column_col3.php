<div class="row">
<?php
$activityInTheFuture = (time() - 7 * 24 * 60 * 60 < strtotime($this->activity->dateTimeEnd));
$formkit = $this->layoutkit->formkit;
$callbackTagsJoinEdit = $formkit->setPostCallback('ActivitiesController', 'joinLeaveActivityCallback');
$callbackTagsCancelUncancel = $formkit->setPostCallback('ActivitiesController', 'cancelUncancelActivityCallback');

$layoutbits = new \MOD_layoutbits();
$request = PRequest::get()->request;
$login_url = 'login/' . htmlspecialchars(implode('/', $request), ENT_QUOTES);
$purifierModule = new MOD_htmlpure();
$purifier = $purifierModule->getActivitiesHtmlPurifier();
$status = array();
if ($this->session->has('ActivityStatus')) {
    $status = $this->session->get('ActivityStatus');
    $this->session->remove('ActivityStatus');
}
if (!empty($status)) {
    echo '<div class="col-12"><div class="alert alert-success" role="alert">' . $words->get($status[0], $status[1]) . '</div></div>';
}
if ($this->activity->status == 1) {
    // the activity has been cancelled
    echo '<div class="col-12"><div class="alert alert-warning w-100" role="alert">' . $words->get('ActivityHasBeenCancelled') . '</div></div>';
}
$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    $errStr = '<div class="col-12"><div class="alert alert-danger" role="alert">';
    foreach ($errors as $error) {
        $errStr .= $words->get($error) . "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</div></div>';
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
    <div class="col-12 col-md-9 order-1">
        <h2 class="m-0"><?php echo $this->activity->title; ?>
            <a class="btn btn-primary"
               title="<?php echo $words->get('activity.report'); ?>"
               href="/feedback?IdCategory=12&FeedbackQuestion=<?php
                  echo urlencode('Hi, I think the activity '
                      . $this->activity->title . ' (' . $this->activity->id
                      . ') isn\'t in line with the rules.') ?>">
                <i class="fas fa-flag"></i>
            </a>
        </h2>
    </div>
    <div class="col-md-3 d-none d-md-block ml-md-auto mb-2 order-2">
        <div class="d-flex flex-row hidden-md-down pull-right">
            <div class="pr-2 align-middle"><?php if ($this->activity->public) { ?><img src="images/online_meeting_noun_3384309.svg" width="32" height="32"><?php } else { ?><i class="fa fa-3x fa-users"></i><?php } ?></div>
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

    <div class="col-12 col-md-6 order-3">
        <input type="hidden" id="location" value="<?= $this->activity->location->name ?>">
        <input type="hidden" id="latitude" value="<?= $this->activity->location->latitude ?>">
        <input type="hidden" id="longitude" value="<?= $this->activity->location->longitude ?>">
        <input type="hidden" id="zoom" value="10">
        <div id="map" class="mappreview m-2 w-100 postleftcolumn" style="height:10rem;"></div>
        <p class="ml-2" style="overflow-wrap: break-word">
            <?php echo $this->activity->address ?><br>
            <span class="mt-1 font-weight-bold"><?php echo $this->activity->location->name . '<br>' . $this->activity->location->getCountry()->name; ?></span></p>
    </div>

    <div class="col-12 col-md-6 order-4">
        <div class="card p-2">
            <div class="d-flex flex-row justify-content-between">
                <div class="h4"><?php echo $words->getSilent('activity.headline.join'); ?></div>
                <div class="float-right">
                <?php
                if ($this->activity->dateStart == $this->activity->dateEnd) {
                    echo '<i class="far fa-calendar-alt mr-1"></i>' . $this->activity->dateStart . '<br>';
                    echo '<i class="far fa-clock mr-1"></i>' . $this->activity->timeStart . ' - ' . $this->activity->timeEnd . '<br>';
                } else {
                    echo '<i class="far fa-calendar-alt mr-1"></i>' . $this->activity->dateStart . ' <i class="far fa-clock mr-1"></i>' . $this->activity->timeStart . '<br>';
                    echo '<i class="far fa-calendar-alt mr-1"></i>' . $this->activity->dateEnd . ' <i class="far fa-clock mr-1"></i>' . $this->activity->timeEnd . '';
                } ?>
                </div>
            </div>
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
                                    <?php echo $words->getSilent('ActivityYes'); ?>
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
                                    <?php echo $words->getSilent('ActivityMaybe'); ?>
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
                                    <?php echo $words->getSilent('ActivityNo'); ?>
                                </label>
                            </div>
                        </div>

                        <input class="o-input mb-1" type="text" maxlength="80" id="activity-comment"
                               name="activity-comment"
                               value="<?php echo htmlspecialchars($vars['activity-comment'], ENT_QUOTES); ?>"
                               placeholder="<?php echo $words->get('ActivityYourComment'); ?>"/>

                        <?php
                        if ($activityInTheFuture) {
                            if ($this->member->status == 0) {
                                echo '<button type="submit" id="activity-join" name="activity-join" class="btn btn-primary btn-block" title="' . $words->getSilent('ActivityJoinTheFun') . '">' . $words->getSilent('ActivityJoinTheFun') . '</button>';
                            } else {
                                echo '<button type="submit" id="activity-update" name="activity-edit" class="btn btn-primary btn-block" title="' . $words->getSilent('ActivityUpdate') . '">' . $words->getSilent('ActivityUpdate') . '</button>';
                                echo '<a class="btn btn-primary btn-block" title="' . $words->getSilent('activity.download') . '" href="activity/' . $this->activity->id . '/download">' . $words->getSilent('activity.download') . '</a>';

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
                <div class="o-form-group mt-3">
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
                        echo '<button type="submit" class="btn btn-danger btn-block" id="activity-cancel" name="activity-cancel"';
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

    <div class="col-12 col-md-4 col-lg-3 mt-3 order-6 order-md-5">
        <h5 class="mb-0"><?php echo $words->get('ActivityOrganizers'); ?></h5>
            <div class="d-flex flex-row">
                <?php
                foreach ($this->activity->organizers as $organizer) { ?>
                    <div class="d-flex mr-2">
                        <div class="mr-2"><a href="members/<?php $organizer->Username; ?>"><img
                                    class="profileimg avatar-48" src="members/avatar/<?php echo $organizer->Username; ?>/48"></a></div>
                        <div>
                            <a href="members/<?php echo $organizer->Username; ?>"><?php echo $organizer->Username; ?></a>
                            <br><a href="new/message/<?php echo $organizer->Username; ?>"><i class="fa fa-envelope mt-3" title="Write organiser"></i></a>
                        </div>
                    </div>
                <?php } ?>
            </div>
    </div>
    <div id="activity-description" class="col-12 col-md-8 col-lg-9 mt-3 order-5 order-md-6">
        <?php echo $purifier->purify($this->activity->description); ?>
    </div>

    <div class="col-12 mt-3 order-7">
        <h5 class="mb-0"><?php echo $words->get('ActivityAttendees'); ?></h5>
    </div>


    <?php if ($this->member) { ?>

        <div class="col-12 order-9">
<div class="row no-gutters">

        <?php
        foreach ($this->attendeesPager->getActiveSubset($this->activity->attendees) as $attendee) {

            ?>
            <div class="col-12 col-sm-6 col-md-4">
                <div class="m-1 p-2
<?php
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
                                class="profileimg avatar-48" src="members/avatar/<?php echo $attendee->Username; ?>/48"></a></div>
                    <div><a href="members/<?php echo $attendee->Username; ?>"><?php echo $attendee->Username; ?></a><br>
                        <small>
                            <?php
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
                <?php if ($attendee->comment) { ?>
                    <div class="small gray"><i><?php echo htmlspecialchars($attendee->comment); ?></i></div>
                <?php } ?>
            </div>
            </div>
            <?php
        }
        ?>
        </div>
        <div class="col-12 order-10">
            <?php
        echo $this->attendeesPager->render();
        ?>
        </div>
        </div>
            <?php
    } else {
        echo '<div class="col-12 order-9"><h3>' . $words->get('ActivityAttendees') . '</h3>';
        echo '<p>' . $words->getBuffered('ActivitiesLogInWhoIsComing', '<a href="' . $login_url . '">', '</a>') . '</p></div>';
    }

    echo $words->flushBuffer(); ?>

<script type="text/javascript">
    $(function () {
        if ($('#map').length) {
            let latitude = $('#latitude').val();
            let longitude = $('#longitude').val();
            let zoom = $('#zoom').val();
            var map = L.map('map', {
                center: [latitude, longitude],
                zoom: zoom
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="/about/credits#OSM">OpenStreetMap contributors</a>',
                subdomains: ['a', 'b', 'c']
            }).addTo(map);
        }
    });
</script>
</div>
