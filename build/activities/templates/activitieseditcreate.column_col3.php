<?php
$formkit = $this->layoutkit->formkit;
$callbackTags = $formkit->setPostCallback('ActivitiesController', 'editCreateActivityCallback');
$callbackTagsCancelUncancel = $formkit->setPostCallback('ActivitiesController', 'cancelUncancelActivityCallback');

$errors = $this->getRedirectedMem('errors');
$vars = $this->getRedirectedMem('vars');
if (empty($vars)) {
    $vars['activity-id'] = $this->activity->id;
    $vars['activity-title'] = $this->activity->title;
    $location = $this->activity->location;
    $vars['activity-location'] = $location->name;
    $vars['activity-location-id'] = $vars['activity-location_geoname_id'] = $location->geonameId;
    $vars['activity-location_latitude'] = $location->latitude;
    $vars['activity-location_longitude'] = $location->longitude;
    $vars['activity-address'] = $this->activity->address;
    $vars['activity-start-date'] = $this->activity->dateTimeStart;
    $vars['activity-end-date'] = $this->activity->dateTimeEnd;
    $vars['activity-description'] = $this->activity->description;
    $vars['activity-public'] = $this->activity->public;
}
?>
<div class="row">
    <div class="col-12">
        <form method="post" id="activity-create-form" name="activity-create-form" autocomplete="off">
            <input type="hidden" id="activity-id" name="activity-id" value="<?php echo $vars['activity-id']; ?>"/>
            <input type="hidden" id="activity-location-id" name="activity-location-id"
                   value="<?php echo $vars['activity-location-id']; ?>"/>

            <?php echo $callbackTags;
            if (!empty($errors)) {
                $errStr = '<div class="row"><div class="col-12"><div class="alert alert-danger col-12" role="alert">';
                foreach ($errors as $error) {
                    $parts = explode("###", $error);
                    if (count($parts) > 1) {
                        $errStr .= $words->get($parts[0], $parts[1]);
                    } else {
                        $errStr .= $words->get($error);
                    }
                    $errStr .= "<br />";
                }
                $errStr = substr($errStr, 0, -6) . '</div></div></div>';
                echo $errStr;
            }
            ?>

            <div class="row mt-3">
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
            </div>

            <div class="row">
                <div class="col-12 col-md-8">
                    <div class="form-group mb-1">
                        <label for="activity-title"
                               class="form-control-label"><?php echo $words->get('ActivityTitle'); ?>
                            *</label>
                        <input type="text" id="activity-title" name="activity-title" maxlength="80" class="form-control"
                               value="<?php echo $vars['activity-title']; ?>"
                               placeholder="<?php echo $words->get('ActivityTitle'); ?>*">
                    </div>
                    <div class="form-group mb-1">
                        <label for="activity-description"
                               class="form-control-label"><?php echo $words->get('ActivityDescription'); ?>*</label>
                        <textarea id="activity-description" name="activity-description" class="form-control editor">
                        <?php
                        if (!empty($vars['activity-description'])) {
                            echo $vars['activity-description'];
                        } ?>
                    </textarea>
                    </div>
                    <div class="form-check mb-1">
                        <input type="checkbox" class="form-check-input" id="activity-public" name="activity-public" value="1"
                            <?php if (isset($vars['activity-public']) && $vars['activity-public']) { echo 'checked="checked"'; } ?>>
                        <label for="activity-public" class="form-check-label"><?php echo $words->get('ActivityOnline'); ?></label>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mb-1">
                        <label for="activity-start-date"><?php echo $words->get('ActivityStart'); ?>*</label>
                        <div class="input-group date"
                             id="activity-start-datepicker"
                             data-target-input="nearest">
                            <div class="input-group-prepend"
                                 data-target="#activity-start-date"
                                 data-toggle="datetimepicker">
                                <div class="input-group-text bg-primary white">
                                    <i class="fa fa-calendar"></i>
                                </div>
                            </div>
                            <input type="text"
                                   id="activity-start-date"
                                   name="activity-start-date"
                                   class="form-control datetimepicker-input"
                                   data-toggle="datetimepicker"
                                   data-target="#activity-start-date" value="<?= $vars['activity-start-date'] ?>" >
                        </div>
                    </div>

                    <div class="form-group mb-1">
                        <label for="activity-end-date"><?php echo $words->get('ActivityEnd'); ?>*</label>
                        <div class="input-group date"
                             id="activity-end-datepicker"
                             data-target-input="nearest">
                            <div class="input-group-prepend"
                                 data-target="#activity-end-date"
                                 data-toggle="datetimepicker">
                                <div class="input-group-text bg-primary white">
                                    <i class="fa fa-calendar"></i>
                                </div>
                            </div>
                            <input type="text"
                                   id="activity-end-date"
                                   name="activity-end-date"
                                   class="form-control datetimepicker-input"
                                   data-toggle="datetimepicker"
                                   data-target="#activity-end-date" value="<?= $vars['activity-end-date'] ?>" >
                        </div>
                    </div>

                    <div class="form-group mb-1">
                        <label for="activity-location"><?php echo $words->getBuffered('ActivitiesLocationSearch'); ?></label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-primary white"><i class="fa fa-globe"></i></span>
                            </div>
                            <input type="text" id="activity-location" name="activity-location"
                                   class="form-control search-picker" value="<?= $vars['activity-location'] ?? ''; ?>"
                                   placeholder="<?php echo $words->get('ActivityLocation'); ?>*">
                        </div>
                    </div>

                    <div class="form-group mb-1">
                        <label for="activity-address" class="mb-0"><?php echo $words->get('ActivityAddress'); ?></label>
                        <textarea id="activity-address" name="activity-address" class="form-control"
                                  rows="3"><?php echo $vars['activity-address']; ?></textarea>
                    </div>
                    <input type="hidden" id="activity-location_geoname_id" name="activity-location_geoname_id" value="<?= $vars['activity-location_geoname_id'] ?? '' ?>">
                    <input type="hidden" id="activity-location_latitude" name="activity-location_latitude" value="<?= $vars['activity-location_latitude'] ?? '' ?>">
                    <input type="hidden" id="activity-location_longitude" name="activity-location_longitude" value="<?= $vars['activity-location_longitude'] ?? '' ?>">
                </div>

                <div class="col-12 mt-3">
                    <?php
                    if ($vars['activity-id'] != 0) {
                        $activitieseditcreatebutton = $words->getSilent('ActivitiesEditCreateUpdate');
                    } else {
                        $activitieseditcreatebutton = $words->getSilent('ActivitiesSubmit');
                    }
                    ?>
                    <button type="submit" class="btn btn-primary" id="activity-submit"
                            name="activity-submit"><?php echo $activitieseditcreatebutton; ?></button><?php echo $words->flushBuffer(); ?>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(function () {
        let activityStartDate = $('#activity-start-date');
        let activityEndDate = $('#activity-end-date');
        activityStartDate.datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            collapse: false,
            sideBySide: false,
            useCurrent: false,
        });
        activityEndDate.datetimepicker({
            format: 'YYYY-MM-DD HH:mm',
            collapse: false,
            sideBySide: false,
            useCurrent: false,
        });

        activityStartDate.on("change.datetimepicker", function (e) {
            activityEndDate.datetimepicker('minDate', e.date);
        });
        activityEndDate.on("change.datetimepicker", function (e) {
            activityStartDate.datetimepicker('maxDate', e.date);
        });
    });
</script>
<script src="build/cktranslations/<?= $this->session->get('lang', 'en');?>.js"></script>

