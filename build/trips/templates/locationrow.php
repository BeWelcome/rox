<?php $words = $this->getWords();
$location = 'location-' . $locationRow; ?>
<div class="bw_row">
<input type="hidden" name="location-subtrip-id[]" id="<?= $location ?>-subtrip-id" value="<?= $locationDetails->subTripId ?>">
<input type="hidden" class="validate collect" name="location-geoname-id[]" id="<?= $location ?>-geoname-id"
       value="<?= $locationDetails->geonameId ?>">
<input type="hidden" class="validate collect" name="location-latitude[]" id="<?= $location ?>-latitude"
       value="<?= $locationDetails->latitude ?>">
<input type="hidden" class="validate collect" name="location-longitude[]" id="<?= $location ?>-longitude"
       value="<?= $locationDetails->longitude ?>">
<div class="form-group has-feedback col-md-5">
    <label for="<?= $location ?>"
           class="control-label sr-only"><?php echo $words->get('TripLocation'); ?></label>

    <div class="input-group">
        <input type="text" class="form-control location-picker validate collect" name="location[]"
               placeholder="<?= $words->getBuffered('TripLocation'); ?>" id="<?= $location ?>"
               value="<?= $locationDetails->name ?>"/>

        <label for="<?= $location ?>"
               class="control-label input-group-addon btn"><span
                class="fa fa-fw fa-map-marker"></span></label>
    </div>
</div>
<div class="form-group has-feedback col-md-3">
    <label for="arrival-<?= $locationRow ?>"
           class="control-label sr-only"><?php echo $words->get('TripDateStart'); ?></label>

    <div class="input-group">
        <input type="text" class="form-control date-picker-start validate" name="location-arrival[]"
               placeholder="<?= $words->getBuffered('TripArrival'); ?>" id="arrival-<?= $locationRow ?>"
               value="<?= $locationDetails->arrival ?>"/>
        <label for="arrival-<?= $locationRow ?>" class="control-label input-group-addon btn"><span
                class="fa fa-fw fa-calendar"></span></label>
    </div>
    <?php
    if (in_array('TripErrorWrongArrivalFormat###' . $locationRow, $errors)) {
        echo '<span class="help-block alert alert-danger">' . $words->get('TripErrorWrongArrivalFormat') . '</span>';
    }
    ?>
</div>
<div class="form-group has-feedback col-md-3">
    <label for="departure-<?= $locationRow ?>"
           class="control-label sr-only"><?php echo $words->get('TripDeparture'); ?></label>

    <div class="input-group">
        <input type="text" class="form-control date-picker-end validate" name="location-departure[]"
               placeholder="<?= $words->getBuffered('TripDeparture'); ?>" id="departure-<?= $locationRow ?>"
               value="<?= $locationDetails->departure ?>"/>
        <label for="departure-<?= $locationRow ?>" class="control-label input-group-addon btn"><span
                class="fa fa-fw fa-calendar"></span></label>
    </div>
    <?php
    if (in_array('TripErrorWrongDepartureFormat###' . $locationRow, $errors)) {
        echo '<span class="help-block alert alert-danger">' . $words->get('TripErrorWrongDepartureFormat') . '</span>';
    }
    ?>
</div>
<div class="form-group col-md-1">
    <label for="remove-<?= $locationRow ?>"
           class="control-label sr-only"><?php echo $words->get('TripRemoveLocation'); ?></label>
    <button name="remove-<?= $locationRow ?>" id="remove-<?= $locationRow ?>" disabled
            class="btn form-control btn-default"><span class="fa fa-fw fa-remove"></span></button>
</div>
</div>
<div class="bw_row">
    <div class="form-group col-md-12"><label for="trip-location-options-<?= $locationRow ?>"
           class="control-label sr-only"><?php echo $words->get('TripLocationOptionsLabel'); ?></label>
        <?= _getOptionsDropdown($locationRow, $locationDetails->options, $words) ?>
    </div>
</div>
