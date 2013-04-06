<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'joinLeaveCancelActivityCallback');
$layoutbits = new Mod_layoutbits(); 
if ($this->activity->status == 1) {
    // the activity has been cancelled ?>
    <div class="error"><?php echo $words->get('ActivityCancelled'); ?></div>
<?php } ?>
<div id="activity">
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
                <div class="row">
                    <?php
                    if ($this->loggedInMember) {
                    ?>
                    <form method="post" id="activity-show-form" class="yform full">
                    <div class="type-text">
                        <h3><?= $words->get('ActivityYourStatus'); ?></h3>
                        <?php echo $callbackTags; ?>
                        <input type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />
                        <label for="activity-comment"><?php echo $words->get('ActivityYourComment'); ?></label>
                        <input type="text" id="activity-comment" name="activity-comment" value="<?php echo $this->member->comment;?>" />
                    </div>
                    <div class="type-button">
                    <?php if ($this->activity->status == 0) { ?>
                    <div class="row">
                    <?php
                        $disabled = 'class="button"';
                        if ($this->member->status == 1) {
                            $disabled = 'disabled="disabled"';
                        }
                    ?>
                    <input type="submit" id="activity-yes" name="activity-yes" value="<?php echo $words->get('ActivityYes'); ?>" <?php echo $disabled; ?> />
                    <?php
                        $disabled = 'class="button"';
                        if ($this->member->status == 2) {
                            $disabled = 'disabled="disabled"';
                        }
                    ?>
                    <input type="submit" id="activity-maybe" name="activity-maybe" value="<?php echo $words->get('ActivityMaybe'); ?>" <?php echo $disabled; ?> /> 
                    <?php
                        $disabled = 'class="button"';
                        if ($this->member->status == 3) {
                            $disabled = 'disabled="disabled"';
                        }
                    ?>
                    <input type="submit" id="activity-no" name="activity-no" value="<?php echo $words->get('ActivityNo'); ?>" <?php echo $disabled; ?> />
                    <?php
                        $disabled = 'class="button"';
                        $disableLeave = ($this->member->status == 0);
                        $disableLeave = $disableLeave || (($this->member->organizer == 1) && (count($this->activity->organizers) == 1));
                        if ($disableLeave) {
                            $disabled = 'disabled="disabled"';
                        }
                    ?>
                    <input type="submit" id="activity-leave" name="activity-leave" value="<?php echo $words->get('ActivityLeave'); ?>" <?php echo $disabled; ?> /> 
                    <?php
                        if (isset($this->member->organizer)) {
                            echo '<input type="submit" class="button" id="activity-cancel" name="activity-cancel" value="' . $words->get('ActivityCancel') . '"/>';
                        }
                    ?>
                    </div>
                    <?php
                    }
                    }?>
                    </form>
                </div>
                <div class="row">
                    <h3><?= $words->get('ActivityDateTime'); ?></h3>
                    <p><?php echo $this->activity->dateStart; ?> - <?php echo $this->activity->dateEnd; ?><br />
                    <?php echo $this->activity->timeStart; ?> - <?php echo $this->activity->timeEnd; ?></p>
                </div>
                <div class="row">
                    <h3><?= $words->get('ActivityLocationAddress'); ?></h3>
                    <p><?php echo $this->activity->address; ?><br />
                    <?php  echo $this->activity->location->name ?>, <?php echo $this->activity->location->getCountry()->name ?></p>
                </div>
            </div> <!-- subcr -->
        </div> <!-- c38r -->
    </div> <!-- subcolums -->
</div>
    <?php if ($this->activity->public || $this->loggedInMember) { ?>
    <div><?php echo $words->get('ActivityAttendees'); echo $this->attendeesPager->render(); ?>
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
                    echo $words->get('ActivityNoIDontAttend');
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
    <div><?php echo $words->get('ActivityOrganizers');?>
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
    </ul></div>
    <?php

    }?>