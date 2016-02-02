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
    $errStr = '<div class="alert alert-warning" role="alert"><strong>';
    foreach ($errors as $error) {
        $parts = explode("###", $error);
        if (count($parts) > 1) {
            $errStr .= $words->get($parts[0], $parts[1]);
        } else {
            $errStr .= $words->get($error);
        }
        $errStr .=  "<br />";
    }
    $errStr = substr($errStr, 0, -6) . '</strong></div>';
    echo $errStr;
}
?>

    <div class="form-group row">
        <small class="pull-xs-right">* <?php echo $words->get('ActivityMandatoryFields'); ?></small>
    </div>

    <div class="form-group row">
        <div class="col-xs-12 col-md-2">
            <label for="activity-title" class="h4 form-control-label pull-xs-right"><?php echo $words->get('ActivityTitle'); ?>*</label>
        </div>
        <div class="col-xs-12 col-md-10">
            <input type="text" id="activity-title" name="activity-title" maxlength="80" class="form-control" value="<?php echo $vars['activity-title']; ?>" placeholder="Give your activity a catching title">
        </div>
    </div>

    <div class="form-group row">
        <div class="col-xs-12 col-md-2">
            <label for="activity-location" class="h4 form-control-label pull-xs-right"><?php echo $words->get('ActivityLocation'); ?>*</label>
        </div>
        <div class="col-xs-12 col-md-7">
                <div class="input-group">
                    <input type="text" id="activity-location" name="activity-location" class="form-control" value="<?php echo $vars['activity-location']; ?>">
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="submit" id="activity-location-button" name="activity-location-button"><?php echo $words->getBuffered('ActivitiesLocationSearch'); ?></button><?php echo $words->flushBuffer(); ?>
                    </span>
                </div>
                <div id="activity-location-suggestion" style="display: none;">
                    <ol id="locations" class="plain"></ol>
                </div>
                <div class="p-t-1">
                    <textarea id="activity-address" name="activity-address" class="form-control" rows="3" cols="60" placeholder="<?php echo $words->get('ActivityAddress'); ?>"><?php echo $vars['activity-address']; ?></textarea>
                </div>
        </div>
        <div class="col-xs-12 col-md-3">
            <fieldset>
                <label for="activity-start-date" class="h4 form-control-label"><?php echo $words->get('ActivityStart'); ?>*</label>
                <input type="text" id="activity-start-date" name="activity-start-date" class="form-control" maxlength="16" value="<?php echo $vars['activity-start-date'];?>" placeholder="0000-00-00 00:00" />
            </fieldset>

            <fieldset>
                <label for="activity-end-date" class="h4 form-control-label"><?php echo $words->get('ActivityEnd'); ?>*</label>
                <input type="text" id="activity-end-date" name="activity-end-date" class="form-control" maxlength="16" value="<?php echo $vars['activity-end-date'];?>" placeholder="0000-00-00 00:00" />
            </fieldset>

        </div>
    </div>

    <div class="form-group row">
        <div class="col-xs-12 col-md-2">
            <label for="activity-description" class="h4 form-control-label pull-xs-right"><?php echo $words->get('ActivityDescription'); ?>*</label>
        </div>
        <div class="col-xs-12 col-md-10">
            <textarea id="activity-description" name="activity-description" class="mce form-control" rows="10" cols="80"><?php echo $vars['activity-description']; ?></textarea>
        </div>
    </div>

    <div class="form-group row m-b-0">
        <div class="col-xs-12 col-md-2"></div>
        <div class="col-xs-12 col-md-10">
                <input type="checkbox" id="activity-public" name="activity-public" checked="checked">&nbsp;<label for="activity-public"><?php echo $words->get('ActivityPublic'); ?></label>
        </div>
    </div>

    <div class="form-group row">
        <div class="col-xs-12 col-md-2"></div>
        <div class="col-xs-12 col-md-10">
            <?php
            if ($vars['activity-id'] != 0) {
                $activitieseditcreatebutton = $words->getSilent('ActivitiesEditCreateUpdate');
            } else {
                $activitieseditcreatebutton = $words->getSilent('ActivitiesSubmit');
            }
            ?>
            <button type="submit" class="btn btn-primary" id="activity-submit" name="activity-submit"><?php echo $activitieseditcreatebutton; ?></button><?php echo $words->flushBuffer(); ?>
        </div>
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
