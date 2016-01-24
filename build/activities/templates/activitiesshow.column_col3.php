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
if (isset($_SESSION['ActivityStatus'])) {
    $status = $_SESSION['ActivityStatus'];
    unset($_SESSION['ActivityStatus']);
}
if (!empty($status)) {
    echo '<div class="success">' . $words->get($status[0], $status[1]) . '</div>';
}
if ($this->activity->status == 1) {
    // the activity has been cancelled
    echo '<div class="note">' . $words->get('ActivityHasBeenCancelled') . '</div>';
}
$errors = $this->getRedirectedMem('errors');
if (!empty($errors)) {
    $errStr = '<div class="error">';
    foreach ($errors as $error) {
        $errStr .= $words->get($error) . "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</div>';
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
<div id="activity">
    <div class="clearfix">
        <h2><?php echo $this->activity->title; ?></h2>
    </div>
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <div class="bw-row">
                    <h3><?= $words->get('ActivityDescription'); ?></h3>
                    <?php echo $purifier->purify($this->activity->description); ?>
                </div>
                <?php if ($this->member) { ?>
                <div><h3><?php echo $words->get('ActivityAttendees');?></h3>
                <?php echo $this->attendeesPager->render(); ?>
                <ul class="clearfix">
                <?php
                    foreach ($this->attendeesPager->getActiveSubset($this->activity->attendees) as $attendee) 
                    {
                        $image = new MOD_images_Image('',$attendee->Username);
                        echo '<li class="picbox_activities float_left">';
                        echo MOD_layoutbits::PIC_50_50($attendee->Username,'',$style='framed float_left');
                        echo '<div class="userinfo">';
                        echo '  <a class="username" href="members/'.$attendee->Username.'">'.$attendee->Username.'</a><br />';
                        echo '  <span class="small"><b>';
                        switch($attendee->status) {
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
                        echo '</b></span><br />';
                        echo '<span class="small">' . htmlspecialchars($attendee->comment) . '</span>';
                        echo '</div>';
                        echo '</li>';
                        }
                    echo '</ul>';
                    echo $this->attendeesPager->render();
                    ?>
                </div>
                <?php
                } else {
                        echo '<div class="bw-row"><h3>' .  $words->get('ActivityAttendees') . '</h3>';
                        echo '<p>'.$words->getBuffered('ActivitiesLogInWhoIsComing', '<a href="' . $login_url . '">', '</a>').'</p>';
                        echo '</div>';
                }?>
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        <div class="c38r">
            <div class="subcr">
                <?php if ($activityInTheFuture) {
                        if ($this->member) {
                            if ($this->activity->status == 0) { ?>
                    <form method="post" id="activity-show-form" class="yform full abitlower">
                    <?php echo $callbackTagsJoinEdit; ?>
                    <input type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />
                    <div class="type-text">
                        <label for="activity-comment"><?php echo $words->get('ActivityYourComment'); ?>:</label>
                        <input type="text" maxlength="80" id="activity-comment" name="activity-comment" value="<?php echo htmlspecialchars($vars['activity-comment'], ENT_QUOTES); ?>" />
                    </div>
                    <div class="type-check">
                        <div class="abitlower"><input type="radio" value="activity-yes" id="activity-yes" name="activity-status" <?php if ($this->member->status == 1) { echo 'checked="checked"'; }?> >&nbsp;<label for="activity-yes"><?php echo $words->getSilent('ActivityYes'); ?></label></div>
                        <?php if (!$this->member->organizer) { ?>
                        <div class="abitlower"><input type="radio" value="activity-maybe" id="activity-maybe" name="activity-status" <?php if ($this->member->status == 2) { echo 'checked="checked"'; }?> >&nbsp;<label for="activity-maybe"><?php echo $words->getSilent('ActivityMaybe'); ?></label></div>
                        <div class="abitlower"><input type="radio" value="activity-no" id="activity-no" name="activity-status" <?php if ($this->member->status == 3) { echo 'checked="checked"'; }?> >&nbsp;<label for="activity-no"><?php echo $words->getSilent('ActivityNo'); ?></label></div>
                    <?php } ?>
                    </div>
                    <div class="type-button">
                    <?php
                        $enabled = 'class="button"';
                        if ($this->member->status == 0) {
                            echo '<button type="submit" id="activity-join" name="activity-join" class="button" title="' . $words->getSilent('ActivityJoinTheFun') . '" >' . $words->getSilent('ActivityJoinTheFun') . '</button>';
                        } else {
                            echo '<button type="submit" id="activity-update" name="activity-edit" class="button" title="' . $words->getSilent('ActivityUpdate') . '" >' . $words->getSilent('ActivityUpdate') . '</button>';
                            if (!$this->member->organizer) {
                                echo '&nbsp;&nbsp;<button type="submit" id="activity-leave" name="activity-leave" class="button back" title="' . $words->getSilent('ActivityLeave') . '" >' . $words->getSilent('ActivityLeave') . '</button>';
                            }
                        }
                    ?>
                    </div>
                    </form>
                    <?php
                        }
                    } else {
                        echo '<div class="row abitright">';
                        echo '<p>'.$words->getBuffered('ActivitiesPleaseLogInToJoinActivity', '<a href="' . $login_url . '">', '</a>').'</p>';
                        echo '</div>';
                    }
                    }?>
                <div class="row abitright">
                    <h3><?= $words->get('ActivityDateTime'); ?></h3>
                    <p><?php echo $this->activity->dateStart; ?><?php 
                    if ($this->activity->dateStart != $this->activity->dateEnd){
                        echo ' - ' . $this->activity->dateEnd;
                    }?><br />
                    <?php echo $this->activity->timeStart; ?> - <?php echo $this->activity->timeEnd; ?></p>
                </div>
                <div class="row abitright">
                    <h3><?= $words->get('ActivityLocationAddress'); ?></h3>
                    <p><?php echo $this->activity->address ?><br />
                    <?php  echo $this->activity->location->name ?>, <?php echo $this->activity->location->getCountry()->name ?></p>
                </div>
                <div class="row abitright">
                    <h3><?= $words->get('ActivityAttendeesNumbersTitle'); ?></h3>
                    <p><?php if ($this->activity->attendeesYes != 0){ echo $words->get('ActivityAttendeesYes', $this->activity->attendeesYes);} ?><br />
                     <?php if ($this->activity->attendeesMaybe != 0){ echo $words->get('ActivityAttendeesMaybe', $this->activity->attendeesMaybe);} ?><br />
                     <?php if ($this->activity->attendeesNo != 0){ echo $words->get('ActivityAttendeesNo', $this->activity->attendeesNo);} ?></p>
                </div>
                <?php if ($this->member) {
                    if ($this->member->organizer == true) { ?>
                    <form method="post" id="activity-show-form" class="yform full abitlower">
                    <div class="type-button">
                        <h3><?php echo $words->get('ActivityOrgaStatusHeadline');?></h3>
                        <?php echo $callbackTagsCancelUncancel; ?>
                        <input class="bw-row" type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />
                        <?php
                            if ($activityInTheFuture) {
                                if ($this->activity->status == 1) { 
                                    echo '<input type="submit" class="button" class="button" id="activity-uncancel" name="activity-uncancel" value="' . $words->getSilent('ActivityUnCancel') . '"/>';
                                } else {
                                    echo '<a href="activities/' . $this->activity->id .'/edit" class="button" style="padding-bottom: 2.5px; padding-top: 4.5px;">' . $words->getSilent('ActivityEdit') . '</a>&nbsp;&nbsp;';
                                    echo '<input type="submit" class="button" class="button" id="activity-cancel" name="activity-cancel" value="' . $words->getSilent('ActivityCancel') . '"/>';
                                }
                            } else {
                                echo $words->getSilent('ActivitityInThePastOrganizer');
                            }
                            echo $words->flushBuffer();
                        ?>
                    </div>
                    </form>
                    <?php 
                    }
                }?>
                <div class="row abitright">
                    <h3><?php echo $words->get('ActivityOrganizers');?></h3>
                    <ul class="clearfix">
                    <?php
                        foreach ($this->activity->organizers as $organizer) {
                            $image = new MOD_images_Image('',$organizer->Username);
                            echo '<li class="picbox_activities float_left">';
                            echo MOD_layoutbits::PIC_50_50($organizer->Username,'',$style='framed float_left');
                            echo '<div class="userinfo">';
                            echo '<a class="username" href="members/'.$organizer->Username.'">'.$organizer->Username.'</a><br />';
                            echo '  <span class="small">' . htmlspecialchars($organizer->comment) . '</span>';
                            echo '</div>';
                            echo '</li>';
                        }
                    ?>
                    </ul>
                </div>
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolums -->
</div>