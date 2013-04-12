<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'editCreateActivityCallback');
if (!isset($disableTinyMCE) || ($disableTinyMCE == 'No')) {
    $textarea = 'activity-description';
    require_once SCRIPT_BASE . 'htdocs/script/tinymceconfig.js';
}
$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['activity-id'] = $this->activity->id;
    $vars['activity-title'] = $this->activity->title;
    $vars['activity-location-id'] = $this->activity->locationId;
    $vars['activity-location'] = $this->activity->location->name . ", " . $this->activity->location->getCountry()->name;
    $vars['activity-address'] = $this->activity->address;
    $vars['activity-start-date'] = $this->activity->dateTimeStart;
    $vars['activity-end-date'] = $this->activity->dateTimeEnd;
    $vars['activity-description'] = $this->activity->description;
    if ($this->activity->public) {
        $vars['activity-public'] = true;
    }
}
?>
<div>
<form method="post" id="activity-create-form">
<input type="hidden" id="activity-id" name="activity-id" value="<?php echo $vars['activity-id']; ?>" />
<input type="hidden" id="activity-location-id" name="activity-location-id" value="<?php echo $vars['activity-location-id']; ?>" /> 
<?php echo $callbackTags; ?>
<fieldset id="activity-create"><legend><?php if ($vars['activity-id'] != 0) {
    echo $words->get('ActivitiesEdit');
} else {
    echo $words->get('ActivitiesCreate');
} ?></legend>
<?php
    if (!empty($errors)) {
        echo '<div class="error">';
        foreach ($errors as $error) {
            echo $words->get($error) . "<br />";
        }
        echo '</div>';
    }
?>
    <div class="row">
        <label for="activity-title"><?php echo $words->get('ActivityTitle'); ?>:</label><br />
        <input type="text" id="activity-title" name="activity-title" maxlength="80" class="long" style="width:99%" value="<?php echo $vars['activity-title']; ?>" />
    </div>
    <div class="row">
        <label for="activity-location"><?php echo $words->get('ActivityLocation'); ?>:</label><br/>
        <input type="text" id="activity-location" name="activity-location" class="long" value="<?php echo $vars['activity-location']; ?>" style="width:70%" />
        <input class="button" type="submit" id="activity-location-button" name="activity-location-button" value="<?php echo $words->getBuffered('ActivitiesLocationSearch'); ?>" /><?php echo $words->flushBuffer(); ?> 
    </div>
    <div id="activity-location-suggestion" style="display: none;">
        <ol id="locations" class="plain"></ol>
    </div>
    <div class="row">
        <label for="activity-address"><?php echo $words->get('ActivityAddress'); ?>:</label><br/>
        <textarea id="activity-address" name="activity-address" rows="3" cols="80" class="long" style="width:99%" ><?php echo $vars['activity-address']; ?></textarea>
    </div>
    <div class="subcolumns row">
    <div class="c50l"><div class="subcl">
        <label for="activity-start-date"><?php echo $words->get('ActivityStart'); ?>:</label><br />
        <input type="text" id="activity-start-date" name="activity-start-date" class="date" maxlength="16" style="width:90%" value="<?php echo $vars['activity-start-date'];?>" />
        </div></div>
        <div class="c50r"><div class="subcr">
        <label for="activity-end-date"><?php echo $words->get('ActivityEnd'); ?>:</label><br />
        <input type="text" id="activity-end-date" name="activity-end-date" class="time" maxlength="16" style="width:98%" value="<?php echo $vars['activity-end-date']; ?>" />
        </div></div>
    </div>
    <div class="subcolumns row">
        <label for="activity-description"><?php echo $words->get('ActivityDescription'); ?>:</label><br/>
        <textarea id="activity-description" name="activity-description" rows="10" cols="80" style="width:99%"><?php echo $vars['activity-description']; ?></textarea>
    </div>
    <div class="subcolumns row">
        <input type="checkbox" id="activity-public" name="activity-public" <?php if (isset($vars['activity-public'])) { echo 'checked="checked"'; } ?>/>&nbsp;<label for="activity-public"><?php echo $words->get('ActivityPublic'); ?></label>
    </div>
    <div class="row">
        <input type="submit" id="activity-submit" name="activity-submit" value="<?php echo $words->getSilent('ActivitiesSubmit'); ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
    </div>
</fieldset>
</form>
</div>
<script type="text/javascript">//<!--
ActivityGeoSuggest.initialize('activity-create-form');
jQuery(function() {
  jQuery( "#activity-start-date" ).datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'HH:mm', minDate: 0, stepMinute: 15 });
});
jQuery(function() {
  jQuery( "#activity-end-date" ).datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'HH:mm', minDate: 0, stepMinute: 15 });
});

var startDateTextBox = jQuery('#activity-start-date');
var endDateTextBox = jQuery('#activity-end-date');

startDateTextBox.datetimepicker({ 
    onClose: function(dateText, inst) {
        if (endDateTextBox.val() != '') {
            var testStartDate = startDateTextBox.datetimepicker('getDate');
            var testEndDate = endDateTextBox.datetimepicker('getDate');
            if (testStartDate > testEndDate)
                endDateTextBox.datetimepicker('setDate', testStartDate);
        }
        else {
            // todo: Add two hours to the selected date/time
            endDateTextBox.val(dateText);
        }
    },
    onSelect: function (selectedDateTime){
        endDateTextBox.datetimepicker('option', 'minDate', startDateTextBox.datetimepicker('getDate') );
    }
});
endDateTextBox.datetimepicker({ 
    onClose: function(dateText, inst) {
        if (startDateTextBox.val() != '') {
            var testStartDate = startDateTextBox.datetimepicker('getDate');
            var testEndDate = endDateTextBox.datetimepicker('getDate');
            if (testStartDate > testEndDate)
                startDateTextBox.datetimepicker('setDate', testEndDate);
        }
        else {
            // todo: Subtract two hours to the selected date/time
            startDateTextBox.val(dateText);
        }
    },
    onSelect: function (selectedDateTime){
        startDateTextBox.datetimepicker('option', 'maxDate', endDateTextBox.datetimepicker('getDate') );
    }
});
//-->
</script>
