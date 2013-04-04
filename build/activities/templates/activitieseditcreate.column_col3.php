<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'editCreateActivityCallback');
if (!isset($disableTinyMCE) || ($disableTinyMCE == 'No')) {
    $textarea = 'activity-description';
    require_once SCRIPT_BASE . 'htdocs/script/tinymceconfig.js';
}
?>
<h3><?php if ($this->editMode) {
    echo $words->get('ActivitiesEdit');
} else {
    echo $words->get('ActivitiesCreate');
} ?></h3>
<div>
<form method="post" id="activity-create-form">
<input type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />
<input type="hidden" id="activity-location-id" name="activity-location-id" value="<?php echo $this->activity->locationId; ?>" /> 
<?php echo $callbackTags; ?>
<fieldset id="activity-create"><legend><?php echo $words->get('ActivitiesCreate'); ?></legend>
<?php
    $errors = array();
    if (isset($mem_redirect->errors)) {
        $errors = $mem_redirect->errors;
    }
    if (count($errors) > 0) {
        echo '<div class="error">';
        foreach ($errors as $error) {
            echo $words->get($error) . "<br />";
        }
        echo '</div>';
    }
?>
    <div class="row">
        <label for="activity-title"><?php echo $words->get('ActivityTitle'); ?>:</label><br />
        <input type="text" id="activity-title" name="activity-title" class="long" style="width:99%" value="<?php echo $this->activity->title; ?>" />
    </div>
    <div class="row">
        <label for="activity-location"><?php echo $words->get('ActivityLocation'); ?>:</label><br/>
        <input type="text" id="activity-location" name="activity-location" class="long" value="<?php 
        if ($this->activity->location) {
            echo $this->activity->location->name
                . ", " . $this->activity->location->getCountry()->name;
        }?>" style="width:70%" /><input class="button" type="submit" id="activity-location-button" name="activity-location-button" value="<?php echo $words->getBuffered('ActivitiesLocationSearch'); ?>" /><?php echo $words->flushBuffer(); ?> 
    </div>
    <div id="activity-location-suggestion" style="display: none;">
        <ol id="locations" class="plain"></ol>
    </div>
    <div class="row">
        <label for="activity-address"><?php echo $words->get('ActivityAddress'); ?>:</label><br/>
        <input type="text" id="activity-address" name="activity-address" class="long" value="<?php echo $this->activity->address; ?>" style="width: 99%"/>
    </div>
    <div class="subcolumns row">
    <div class="c50l"><div class="subcl">
        <label for="activity-start-date"><?php echo $words->get('ActivityDateStart'); ?>:</label><br />
        <input type="text" id="activity-start-date" name="activity-start-date" class="date" maxlength="10" style="width:90%" value="<?php echo $this->activity->dateStart;?>" />
            <script type="text/javascript">
                /*<[CDATA[*/
                var datepicker	= new DatePicker({
                relative	: 'activity-start-date',
                language	: '<?=isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en'?>',
                current_date : '<?php echo $this->activity->dateStart; ?>', 
                topOffset   : '25',
                relativeAppend : true
                });
                /*]]>*/
            </script>
        </div></div>
        <div class="c50r"><div class="subcr">
        <label for="activity-start-time"><?php echo $words->get('ActivityTimeStart'); ?></label><br />
        <input type="text" id="activity-start-time" name="activity-start-time" class="time" maxlength="10" style="width:98%" value="<?php echo $this->activity->timeStart; ?>" />
        </div></div>
    </div>
    <div class="subcolumns row">
    <div class="c50l"><div class="subcl">
        <label for="activity-start-date"><?php echo $words->get('ActivityDateEnd'); ?></label><br />
        <input type="text" id="activity-end-date" name="activity-end-date" class="date" maxlength="10" style="width:90%" value="<?php echo $this->activity->dateEnd; ?>" />
            <script type="text/javascript">
                /*<[CDATA[*/
                var datepicker	= new DatePicker({
                relative	: 'activity-end-date',
                language	: '<?=isset($_SESSION['lang']) ? $_SESSION['lang'] : 'en'?>',
                current_date : '<?php echo $this->activity->dateEnd; ?>', 
                topOffset   : '25',
                relativeAppend : true
                });
                /*]]>*/
            </script>
        </div></div>
        <div class="c50r"><div class="subcr">
        <label for="activity-start-time"><?php echo $words->get('ActivityDateEnd'); ?>:</label><br />
        <input type="text" id="activity-end-time" name="activity-end-time" class="time" maxlength="10" style="width:98%" value="<?php echo $this->activity->timeEnd; ?>" />
        </div></div>
    </div>
    <div class="subcolumns row">
        <label for="activity-description"><?php echo $words->get('ActivityDescription'); ?>:</label><br/>
        <textarea id="activity-description" name="activity-description" rows="10" cols="80" style="width:99%"><?php echo $this->activity->description; ?></textarea>
    </div>
    <div class="subcolumns row">
        <input type="checkbox" id="activity-public" name="activity-public" <?php if ($this->activity->public) { echo 'checked="checked"'; } ?>/>&nbsp;<label for="activity-public"><?php echo $words->get('ActivityPublic'); ?>:</label>
    </div>
    <div class="row">
        <input type="submit" id="activity-submit" name="activity-submit" value="<?php echo $words->get('ActivitiesSubmit'); ?>" class="submit" />
    </div>
</fieldset>
</form>
</div>
<script type="text/javascript">//<!--
ActivityGeoSuggest.initialize('activity-create-form');
//-->
</script>
