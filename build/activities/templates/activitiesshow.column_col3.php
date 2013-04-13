<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'joinLeaveCancelActivityCallback');
$layoutbits = new Mod_layoutbits(); 
if ($this->activity->status == 1) {
    // the activity has been cancelled ?>
    <div class="error"><?php echo $words->get('ActivityCancelled'); ?></div>
<?php } ?>
<div id="activity">
    <div class="floatbox">
        <h2 class="float_left" style="width: 75%;"><?php echo $this->activity->title; ?></h2>
    </div>
    <div class="subcolumns">
        <div class="c62l">
            <div class="subcl">
                <div class="row">
                    <h3><?= $words->get('ActivityDescription'); ?></h3>
                    <span><?php echo $this->activity->description; ?></span>
                </div>             
            </div> <!-- subcl -->
        </div> <!-- c62l -->
        <div class="c38r">
            <div class="subcr">
                <?php if ($this->loggedInMember) {
                        if ($this->activity->status == 0) { ?>
                    <form method="post" id="activity-show-form" class="yform full">
                    <?php echo $callbackTags; ?>
                    <input type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />
                    <div class="type-text">
                        <div class="subcolumns">
                            <div class="c38l">
                                <div class="subcl">
                                    <h3 class="abitlower"><?= $words->get('ActivityMyStatus'); ?></h3>
                                </div> <!-- subcl -->
                            </div> <!-- c38l -->
                            <div class="c62r">
                                <div class="subcl float_right">
                                    <?php
                                        $disabled = 'class="button"';
                                        if ($this->member->status == 1) {
                                            $disabled = 'disabled="disabled" class="button back"';
                                        }
                                    ?>
                                    <button type="submit" id="activity-yes" name="activity-yes" <?php echo $disabled; ?> title="<?php echo $words->getSilent('ActivityYes'); ?>" >
                                    <i class="icon-ok-sign"></i></button><?php echo $words->flushBuffer(); ?>
                                    <?php
                                        $disabled = 'class="button"';
                                        if ($this->member->status == 2) {
                                            $disabled = 'disabled="disabled" class="button back"';
                                        }
                                    ?>
                                    <button type="submit" id="activity-maybe" name="activity-maybe" <?php echo $disabled; ?> title="<?php echo $words->getSilent('ActivityMaybe'); ?>" >
                                    <i class="icon-question-sign"></i></button><?php echo $words->flushBuffer(); ?>
                                    <?php
                                        $disabled = 'class="button"';
                                        if ($this->member->status == 3) {
                                            $disabled = 'disabled="disabled" class="button back"';
                                        }
                                    ?>
                                    <button type="submit" id="activity-no" name="activity-no" <?php echo $disabled; ?> title="<?php echo $words->getSilent('ActivityNo'); ?>" >
                                    <i class="icon-minus-sign"></i></button><?php echo $words->flushBuffer(); ?>
                                </div> <!-- subcl -->
                            </div> <!-- c62r -->
                        </div>
                        <label for="activity-comment"><?php echo $words->get('ActivityYourComment'); ?>:</label>
                        <input type="text" id="activity-comment" name="activity-comment" value="<?php echo $this->member->comment;?>" />
                    </div>
                    <div class="type-button">
                    <?php
                        $disabled = 'class="button"';
                        $disableLeave = ($this->member->status != 0);
                        $disableLeave = $disableLeave || (($this->member->organizer == 1) && (count($this->activity->organizers) == 1));
                        if ($disableLeave) {
                            $disabled = 'disabled="disabled" class="button back"';
                        }
                    ?>
                    <input type="submit" id="activity-leave" name="activity-leave" value="<?php echo $words->getSilent('ActivityLeave'); ?>" <?php echo $disabled; ?> /><?php echo $words->flushBuffer(); ?> 
                    <?php
                        if (isset($this->member->organizer) && ($this->member->organizer == 1)) {
                            if ($this->activity->status == 1) {
                                echo '<input type="submit" class="button" id="activity-uncancel" name="activity-uncancel" value="' . $words->getSilent('ActivityUnCancel') . '"/>';
                            } else {
                                echo '<input type="submit" class="button" id="activity-cancel" name="activity-cancel" value="' . $words->getSilent('ActivityCancel') . '"/>';
                            }
                            echo $words->flushBuffer();
                        }
                    ?>
                    </div>
                    </form>
                    <?php
                        }
                    }?>
                <div class="row abitright">
                    <h3><?= $words->get('ActivityDateTime'); ?>:</h3>
                    <p style="text-align: center;"><?php echo $this->activity->dateStart; ?> - <?php echo $this->activity->dateEnd; ?><br />
                    <?php echo $this->activity->timeStart; ?> - <?php echo $this->activity->timeEnd; ?></p>
                </div>
                <div class="row abitright">
                    <h3><?= $words->get('ActivityLocationAddress'); ?>:</h3>
                    <p><?php echo $this->activity->address; ?><br />
                    <?php  echo $this->activity->location->name ?>, <?php echo $this->activity->location->getCountry()->name ?></p>
                </div>
                <div class="row abitright">
                    <h3><?php echo $words->get('ActivityOrganizers');?></h3>
                    <ul class="floatbox">
                    <?php
                        foreach ($this->activity->organizers as $organizer) 
                        {
                            $image = new MOD_images_Image('',$organizer->Username);
                            echo '<li class="userpicbox float_left">';
                            echo MOD_layoutbits::PIC_50_50($organizer->Username,'',$style='framed float_left');
                            echo '<div class="userinfo">';
                            echo '  <a class="username" href="members/'.$attendee->organizer.'">'.$organizer->Username.'</a><br />';
                            echo '  <span class="small">';
                            switch($organizer->status) {
                                case 1: 
                                    echo $words->get('ActivityYesIAttend');
                                    break;
                                case 2:
                                    echo $words->get('ActivityIMightAttend');
                                    break;
                                case 3:
                                    echo $words->get('ActivityNoIDontAttend');
                                    break;
                            }
                            echo '</div>';
                            echo '</li>';
                        }
                    ?>
                    </ul>
                </div>
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolums -->
    <?php if ($this->activity->public || $this->loggedInMember) { ?>
    <div><h3><?php echo $words->get('ActivityAttendees');?></h3>
    <?php echo $this->attendeesPager->render(); ?>
    <ul class="floatbox">
    <?php
        foreach ($this->attendeesPager->getActiveSubset($this->activity->attendees) as $attendee) 
        {
            $image = new MOD_images_Image('',$attendee->Username);
            echo '<li class="userpicbox float_left">';
            echo MOD_layoutbits::PIC_50_50($attendee->Username,'',$style='framed float_left');
            echo '<div class="userinfo">';
            echo '  <a class="username" href="members/'.$attendee->Username.'">'.$attendee->Username.'</a><br />';
            echo '  <span class="small">';
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
            echo '  </span><br />';
            echo '  <span class="small">' . $attendee->comment . '</span>';
            echo '</div>';
            echo '</li>';
            }
        echo $this->attendeesPager->render();
    ?>
    </ul></div>
    <?php
    }?>
</div>