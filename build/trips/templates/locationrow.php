<?php $words = new MOD_words();
$location = 'location-' . $locationRow; ?>
<input type="hidden" name="location-geoname-id[]" id="<?= $location ?>-geoname-id" value="<?= $locationDetails->geonameId ?>">
<input type="hidden" name="location-latitude[]" id="<?= $location ?>-latitude" value="<?= $locationDetails->latitude ?>">
<input type="hidden" name="location-longitude[]" id="<?= $location ?>-longitude" value="<?= $locationDetails->longitude ?>">
<div class="form-group has-feedback col-md-5">
    <label for="<?= $location ?>"
           class="control-label sr-only"><?php echo $words->get('TripLocation'); ?></label>

    <div class="input-group">
        <input type="text" class="form-control location-picker" name="location[]"
               placeholder="<?= $words->getBuffered('TripLocation'); ?>" id="<?= $location ?>"
               value="<?= $locationDetails->name ?>"/>

        <label for="<?= $location ?>"
               class="control-label input-group-addon btn"><span
                    class="fa fa-fw fa-map-marker"></span></label>
    </div>
    </div>
    <div class="form-group has-feedback col-md-3">
        <label for="startDate-<?= $locationRow ?>"
               class="control-label sr-only"><?php echo $words->get('TripDateStart'); ?></label>

        <div class="input-group">
            <input type="text" class="form-control date-picker-start" name="location-start-date[]"
                   placeholder="<?= $words->getBuffered('TripDateStart'); ?>" id="start-date-<?= $locationRow ?>"
                   value="<?= $locationDetails->startDateString ?>"/>
            <label for="start-date-<?= $locationRow ?>" class="control-label input-group-addon btn"><span
                    class="fa fa-fw fa-calendar"></span></label>
        </div>
        <?php
        if (in_array('TripErrorStartDateWrongFormat###' . $locationRow, $errors)) {
            echo '<span class="help-block alert alert-danger">' . $words->get('TripErrorStartDateWrongFormat') . '</span>';
        }
        ?>
    </div>
    <div class="form-group has-feedback col-md-3">
        <label for="endDate-<?= $locationRow ?>"
               class="control-label sr-only"><?php echo $words->get('TripDateEnd'); ?></label>

        <div class="input-group">
            <input type="text" class="form-control date-picker-end" name="location-end-date[]"
                   placeholder="<?= $words->getBuffered('TripDateStart'); ?>" id="end-date-<?= $locationRow ?>"
                   value="<?= $locationDetails->endDateString ?>"/>
            <label for="end-date-<?= $locationRow ?>" class="control-label input-group-addon btn"><span
                    class="fa fa-fw fa-calendar"></span></label>
        </div>
        <?php
        if (in_array('TripErrorEndDateWrongFormat###' . $locationRow, $errors)) {
            echo '<span class="help-block alert alert-danger">' . $words->get('TripErrorEndDateWrongFormat') . '</span>';
        }
        ?>
    </div>
<div class="form-group col-md-1">
    <label for="remove-<?= $locationRow ?>"
           class="control-label sr-only"><?php echo $words->get('TripRemoveLocation'); ?></label>
    <button name="remove-<?= $locationRow ?>" id="remove-<?= $locationRow ?>" disabled class="btn form-control btn-default"><span class="fa fa-fw fa-remove"></span></button>
</div>

