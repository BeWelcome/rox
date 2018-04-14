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

<form method="post" id="activity-create-form" name="activity-create-form">
    <input type="hidden" id="activity-id" name="activity-id" value="<?php echo $vars['activity-id']; ?>" />
    <input type="hidden" id="activity-location-id" name="activity-location-id" value="<?php echo $vars['activity-location-id']; ?>" />

    <?php echo $callbackTags;
    if (!empty($errors)) {
        $errStr = '<div class="row"><div class="alert alert-danger col-sm-12" role="alert"><strong>';
        foreach ($errors as $error) {
            $parts = explode("###", $error);
            if (count($parts) > 1) {
                $errStr .= $words->get($parts[0], $parts[1]);
            } else {
                $errStr .= $words->get($error);
            }
            $errStr .=  "<br />";
        }
        $errStr = substr($errStr, 0, -6) . '</strong></div></div>';
        echo $errStr;
    }
    ?>

    <div class="row mt-3 mb-1 justify-content-center">
        <div class="col-12">
            <small class="pull-right">* <?php echo $words->get('ActivityMandatoryFields'); ?></small>
            <h2 id="activity-create">
                <?php if ($vars['activity-id'] != 0) {
                        echo $words->get('ActivitiesEdit');
                    } else {
                        echo $words->get('ActivitiesCreate');
                    } ?>
            </h2>
        </div>

        <div class="w-100 mt-3"></div>

        <div class="col-12 col-md-6">
            <label for="activity-start-date"
                   class="control-label sr-only"><?php echo $words->get('ActivityStart'); ?>*</label>
            <div class="input-group date" id="date-time-start" data-target-input="nearest">
                <input type="text" id="activity-start-date" name="activity-start-date" class="form-control vaidate" data-target="#date-time-start"
                       value="<?php echo $vars['activity-start-date'];?>" placeholder="<?php echo $words->get('ActivityStart'); ?>*" />
                <div class="input-group-append" data-target="#date-time-start" data-toggle="datetimepicker">
                    <span class="input-group-text">
                        <i class="fa fa-calendar"></i>
                    </span>
                </span>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <label for="activity-start-end"
                   class="control-label sr-only"><?php echo $words->get('ActivityStart'); ?>*</label>
            <div class="input-group date" id="date-time-end" data-target-input="nearest">
                <input type="text" id="activity-end-date" name="activity-end-date" class="form-control vaidate" data-target="#date-time-end"
                       value="<?php echo $vars['activity-end-date'];?>" placeholder="<?php echo $words->get('ActivityEnd'); ?>*" />
                <div class="input-group-append" data-target="#date-time-end" data-toggle="datetimepicker">
                    <span class="input-group-text">
                        <i class="fa fa-calendar"></i>
                    </span>
                </span>
            </div>
        </div>

        <div class="col-12 mt-3">
            <input type="text" id="activity-title" name="activity-title" maxlength="80" class="form-control" value="<?php echo $vars['activity-title']; ?>" placeholder="<?php echo $words->get('ActivityTitle'); ?>*">
        </div>

        <div class="col-12 mt-3">
            <div class="input-group">
                <input type="text" id="activity-location" name="activity-location" class="form-control" value="<?php echo $vars['activity-location']; ?>" placeholder="<?php echo $words->get('ActivityLocation'); ?>*">
                <span class="input-group-append">
                        <button class="btn btn-primary" type="submit" id="activity-location-button" name="activity-location-button"><?php echo $words->getBuffered('ActivitiesLocationSearch'); ?></button><?php echo $words->flushBuffer(); ?>
                    </span>
            </div>
            <div id="activity-location-suggestion" style="display: none;">
                <ol id="locations" class="plain"></ol>
            </div>
            <div class="mt-1">
                <textarea id="activity-address" name="activity-address" class="form-control" rows="3" cols="60" placeholder="<?php echo $words->get('ActivityAddress'); ?>"><?php echo $vars['activity-address']; ?></textarea>
            </div>
        </div>

        <div class="col-12 mt-3">
            <textarea id="activity-description" name="activity-description" class="long" rows="10" cols="80">
                <?php
                if (!empty($vars['activity-description'])){
                    echo $vars['activity-description'];
                } else {
                    echo $words->get('ActivityDescription');
                }  ?>
            </textarea>
        </div>

        <div class="col-12 mt-3">
            <input type="checkbox" id="activity-public" name="activity-public">&nbsp;<label for="activity-public"><?php echo $words->get('ActivityPublic'); ?></label>
        </div>

        <div class="col-12">
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
    <div class="form-group row justify-content-center">

        <div class="col-12">
            <?php echo $callbackTagsCancelUncancel; ?>
            <input class="row" type="hidden" id="activity-id" name="activity-id" value="<?php echo $this->activity->id; ?>" />
            <?php
            if (!$this->activity->status == 1 && $vars['activity-id'] != 0) {
                echo '<button type="submit" class="btn btn-danger" id="activity-cancel" name="activity-cancel">' . $words->getSilent('ActivityEditCreateCancel') . '</button>';
            }
            ?>
        </div>

    </div>
</form>

<?php
if (!isset($disableTinyMCE) || ($disableTinyMCE == 'No')) {
    $textarea = 'activity-description';
    require_once SCRIPT_BASE . 'web/script/tinymceconfig_php.js';
}
?>
