<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'joinLeaveCancelActivityCallback');
$layoutbits = new Mod_layoutbits(); ?><div>
<form method="post" id="activity-show-form">
<?php echo $callbackTags; ?>
<fieldset>
    <?php
        if ($this->activity->status == 1) {
            // the activity has been cancelled ?>
        <div class="error"><?php echo $words->get('ActivityCancelled'); ?></div>
    <?php } ?>
    <legend><?php echo $words->get('ActivitiesDetails'); ?></legend>
    <input type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />
    <div class="row">
        <label for="activity-title"><?php echo $words->get('ActivityTitle'); ?>:</label><br />
        <input type="text" id="activity-title" name="activity-title" class="long" style="width:99%" value="<?php echo $this->activity->title; ?>" disabled="disabled"/>
    </div>
    <div class="row">
        <label for="activity-address"><?php echo $words->get('ActivityLocation'); ?>:</label><br/>
        <input type="text" id="activity-location" name="activity-location" class="long" value="" style="width: 99%"  disabled="disabled"/>
    </div>
    <div class="row">
        <label for="activity-address"><?php echo $words->get('ActivityAddress'); ?>:</label><br/>
        <input type="text" id="activity-address" name="activity-address" class="long" value="<?php echo $this->activity->address; ?>"  disabled="disabled" style="width: 99%"/>
    </div>
    <div class="subcolumns row">
    <div class="c50l"><div class="subcl">
        <label for="activity-start-date"><?php echo $words->get('ActivityDateStart'); ?>:</label><br />
        <input type="text" id="activity-start-date" name="startdate" class="date" maxlength="10" style="width:90%" value="<?php echo $this->activity->dateStart; ?>"  disabled="disabled" /></div></div>
        <div class="c50r"><div class="subcr">
        <label for="activity-start-time"><?php echo $words->get('ActivityTimeStart'); ?></label><br />
        <input type="text" id="activity-start-time" name="activity-start-time" class="time" maxlength="10" style="width:98%" value="<?php echo $this->activity->timeStart; ?>"  disabled="disabled" />
        </div></div>
    </div>
    <div class="subcolumns row">
    <div class="c50l"><div class="subcl">
        <label for="activity-start-date"><?php echo $words->get('ActivityDateEnd'); ?></label><br />
        <input type="text" id="activity-end-date" name="activity-end-date" class="date" maxlength="10" style="width:90%" value="<?php echo $this->activity->dateEnd; ?>"  disabled="disabled" /></div></div>
        <div class="c50r"><div class="subcr">
        <label for="activity-start-time"><?php echo $words->get('ActivityDateEnd'); ?>:</label><br />
        <input type="text" id="activity-end-time" name="activity-end-time" class="time" maxlength="10" style="width:98%" value="<?php echo $this->activity->timeEnd; ?>"  disabled="disabled" />
        </div></div>
    </div>
    <div class="subcolumns row">
            <label for="activity-description"><?php echo $words->get('ActivityDateDescription'); ?>:</label><br/>
            <textarea id="activity-description" name="activity-description" rows="6" cols="80" disabled="disabled"  style="width:99%"><?php echo $this->activity->description; ?></textarea>
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
    if ($this->loggedInMember) {
    ?>
    <div class="subcolumns row">
    <label for="activity-comment"><?php echo $words->get('ActivityComment'); ?></label><br />
    <input type="text" id="activity-comment" name="activity-comment" style="width:99%" value="<?php echo $this->member->comment;?>" />
    </div>
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
        if ($this->member->organizer) {
            echo '<input type="submit" class="button" id="activity-cancel" name="activity-cancel" value="' . $words->get('ActivityCancel') . '"/>';
        }
    ?>
    </div>
    <?php
    }
    }
    }?>
</fieldset>
</form></div>