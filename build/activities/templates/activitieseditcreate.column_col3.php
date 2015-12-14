<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'editCreateActivityCallback');
$callbackTagsCancelUncancel = $formkit->setPostCallback('ActivitiesController', 'cancelUncancelActivityCallback');

$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['activity-id'] = $this->activity->id;
    $vars['activity-title'] = $this->activity->title;
    $vars['activity-location-id'] = 0;
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
<fieldset id="activity-create"><legend><?php if ($vars['activity-id'] != 0) {
    echo $words->get('ActivitiesEdit');
} else {
    echo $words->get('ActivitiesCreate');
} ?></legend>
<form method="post" id="activity-create-form">
<input type="hidden" id="activity-id" name="activity-id" value="<?php echo $vars['activity-id']; ?>" />
<input type="hidden" id="activity-location-id" name="activity-location-id" value="<?php echo $vars['activity-location-id']; ?>" />
<?php echo $callbackTags;
if (!empty($errors)) {
    $errStr = '<div class="error">';
    foreach ($errors as $error) {
        $parts = explode("###", $error);
        if (count($parts) > 1) {
            $errStr .= $words->get($parts[0], $parts[1]);
        } else {
            $errStr .= $words->get($error);
        }
        $errStr .=  "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</div>';
    echo $errStr;
}
?>
    <div class="bw-row">
        <label class="float_left"for="activity-title"><?php echo $words->get('ActivityTitle'); ?>*</label><span class="small float_right" style="margin-right: 0.3em;">* <?php echo $words->get('ActivityMandatoryFields'); ?></span><br />
        <input type="text" id="activity-title" name="activity-title" maxlength="80" class="long" style="width:99%" value="<?php echo $vars['activity-title']; ?>" />
    </div>
    <div class="bw-row">
        <label for="activity-location"><?php echo $words->get('ActivityLocation'); ?>*</label><br/>
        <input type="text" id="activity-location" name="activity-location" class="long" value="<?php echo $vars['activity-location']; ?>" style="width:70%" />
        <input class="button" type="submit" id="activity-location-button" name="activity-location-button" value="<?php echo $words->getBuffered('ActivitiesLocationSearch'); ?>" /><?php echo $words->flushBuffer(); ?>
    </div>
    <div id="activity-location-suggestion" style="display: none;">
        <ol id="locations" class="plain"></ol>
    </div>
    <div class="bw-row">
        <label for="activity-address"><?php echo $words->get('ActivityAddress'); ?>*</label><br/>
        <textarea id="activity-address" name="activity-address" class="nomce" rows="3" cols="80" class="long" style="width:99%" ><?php echo $vars['activity-address']; ?></textarea>
    </div>
    <div class="subcolumns bw_row">
    <div class="c50l"><div class="subcl">
        <label for="activity-start-date"><?php echo $words->get('ActivityStart'); ?>*</label><br />
        <input type="text" id="activity-start-date" name="activity-start-date" class="date" maxlength="16" style="width:90%" value="<?php echo $vars['activity-start-date'];?>" />
        </div></div>
        <div class="c50r"><div class="subcr">
        <label for="activity-end-date"><?php echo $words->get('ActivityEnd'); ?>*</label><br />
        <input type="text" id="activity-end-date" name="activity-end-date" class="time" maxlength="16" style="width:98%" value="<?php echo $vars['activity-end-date']; ?>" />
        </div></div>
    </div>
    <div class="subcolumns bw_row">
        <label for="activity-description"><?php echo $words->get('ActivityDescription'); ?>*</label><br/>
        <textarea id="activity-description" name="activity-description" class="mce" rows="10" cols="80" style="width:99%"><?php echo $vars['activity-description']; ?></textarea>
    </div>
    <div class="subcolumns bw_row">
        <input type="checkbox" id="activity-public" name="activity-public" <?php if (isset($vars['activity-public'])) { echo 'checked="checked"'; } ?>/>&nbsp;<label for="activity-public"><?php echo $words->get('ActivityPublic'); ?></label>
    </div>
    <div class="subcolumns bw_row">
        <?php
        if ($vars['activity-id'] != 0) {
             $activitieseditcreatebutton = $words->getSilent('ActivitiesEditCreateUpdate');
        } else {
             $activitieseditcreatebutton = $words->getSilent('ActivitiesSubmit');
        }
        ?>
        <input type="submit" class="button" id="activity-submit" name="activity-submit" value="<?php echo $activitieseditcreatebutton; ?>" class="submit" /><?php echo $words->flushBuffer(); ?>
    </div>
</form>
<form method="post" id="activity-show-form">
    <div class="bw-row">
        <?php echo $callbackTagsCancelUncancel; ?>
        <input class="bw-row" type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />
        <?php
            if (!$this->activity->status == 1 && $vars['activity-id'] != 0) {
                echo '<input type="submit" class="button" class="back" id="activity-cancel" name="activity-cancel" value="' . $words->getSilent('ActivityEditCreateCancel') . '"/>';
            }
        ?>
    </div>
</form>
</div>
</fieldset>

<script type="text/javascript">//<!--
ActivityGeoSuggest.initialize('activity-create-form');

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
            var endDate = startDateTextBox.datetimepicker('getDate').getTime() + 7200000;
            endDateTextBox.datetimepicker('setDate', new Date(endDate));
        }
    },
    onSelect: function (selectedDateTime){
        endDateTextBox.datetimepicker('option', 'minDate', startDateTextBox.datetimepicker('getDate') );
    },
    dateFormat: 'yy-mm-dd',
    timeFormat: 'HH:mm',
    minDate: new Date(<?php echo time(); ?>),
    stepMinute: 15
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
            var startDate = endDateTextBox.datetimepicker('getDate').getTime() - 7200000;
            startDateTextBox.datetimepicker('setDate', new Date(startDate));
        }
    },
    onSelect: function (selectedDateTime){
        startDateTextBox.datetimepicker('option', 'maxDate', endDateTextBox.datetimepicker('getDate') );
    },
    dateFormat: 'yy-mm-dd',
    timeFormat: 'HH:mm',
    minDate: new Date(<?php echo time(); ?>),
    stepMinute: 15
});
//-->
</script>
