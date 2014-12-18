<?php $words = new MOD_words();
$location = 'location-' . $locationRow; ?>
    <input type="hidden" name="location-geoname-id[]" id="<?= $location ?>-geoname-id" >
    <input type="hidden" name="location-latitude[]" id="<?= $location ?>-latitude" >
    <input type="hidden" name="location-longitude[]" id="<?= $location ?>-longitude" >
    <div class="form-group has-feedback col-md-6">
        <label for="<?= $location ?>"
               class="control-label sr-only"><?php echo $words->get('TripLocation'); ?></label>

        <div class="input-group">
            <input type="text" class="form-control location-picker" name="location[]"
                   placeholder="<?= $words->getBuffered('TripLocation'); ?>" id="<?= $location ?>"
                   value=""/>
            <label for="<?= $location ?>" class="control-label input-group-addon btn"><span
                    class="fa fa-map-marker"></span></label>
        </div>
    </div>
    <div class="form-group has-feedback col-md-3">
        <label for="startdate-<?= $locationRow ?>"
               class="control-label sr-only"><?php echo $words->get('TripDateStart'); ?></label>

        <div class="input-group">
            <input type="text" class="form-control date-picker-start" name="startdate[]"
                   placeholder="<?= $words->getBuffered('TripDateStart'); ?>" id="startdate-<?= $locationRow ?>"
                   value=""/>
            <label for="startdate-<?= $locationRow ?>" class="control-label input-group-addon btn"><span
                    class="fa fa-calendar"></span></label>
        </div>
    </div>
    <div class="form-group has-feedback col-md-3">
        <label for="enddate-<?= $locationRow ?>"
               class="control-label sr-only"><?php echo $words->get('TripDateEnd'); ?></label>

        <div class="input-group">
            <input type="text" class="form-control date-picker-end" name="enddate[]"
                   placeholder="<?= $words->getBuffered('TripDateEnd'); ?>" id="enddate-<?= $locationRow ?>"
                   value=""/>
            <label for="enddate-<?= $locationRow ?>" class="control-label input-group-addon btn"><span
                    class="fa fa-calendar"></span></label>
        </div>
    </div>

