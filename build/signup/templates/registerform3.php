<?php

$map_conf = PVars::getObj('map');
?>
<input type="hidden" id="osm-tiles-provider-base-url"
       value="<?php echo($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key"
       value="<?php echo($map_conf->osm_tiles_provider_api_key); ?>"/>
<input type="hidden" id="marker_label_text" value="<?= $words->get('profile.setlocation.marker'); ?>">

    <div class="card card-block w-100">
        <form method="post" class="form" name="geo-form-js" id="geo-form-js">
            <?= $callback_tag ?>
            <?php $locationError = in_array('SignupErrorProvideLocation', $vars['errors']); ?>
            <div class="row">
                <div class="col-12 col-md-3">

                    <h4 class="text-center mb-2"><?= $words->getFormatted('signup.step', 3); ?></h4>

                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 60%;" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100"><span class="white">60%</span></div>
                    </div>

                    <div class="h4 text-center d-none d-md-block mt-1">
                        <div class="my-3"><i class="fa fa-user"></i><br><a href="signup/1"><?php echo $words->get('LoginInformation'); ?></a></div>
                        <div class="my-3"><i class="fa fa-tag"></i><br><a href="signup/2"><?php echo $words->get('SignupName'); ?></a></div>
                        <div class="my-3"><i class="fa fa-map-marker-alt"></i><br><?php echo $words->get('Location'); ?></div>
                        <div class="my-3 text-muted"><i class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?></div>
                    </div>

                </div>

                <div class="col-12 col-md-9">

                    <div class="o-form-group">
                        <label for="location" class="o-input-label"><?= $words->getSilent('label_setlocation') ?></label>
                        <?php echo $words->flushBuffer(); ?>
                        <div id="set_location_autocomplete" class="js-location-picker autocomplete u-w-full u-z-[1001]">
                        <input id="set_location" name="set_location"
                               class="o-input autocomplete-input"
                               placeholder="<?= $words->get('label_setlocation') ?>"
                               aria-label="<?= $words->get('label_setlocation') ?>"
                            <?php
                            echo isset($vars['location']) ? 'value="'. htmlentities($vars['location'],ENT_COMPAT, 'utf-8') . '" ' : '';
                            ?>
                        >
                        <ul class="autocomplete-result-list"></ul>
                        <ul id="no-results" class="autocomplete-result-list" visible="false">
                            <li class="autocomplete-result">
                                {{ 'suggest.no.results'|trans }}
                            </li>
                        </ul>
                    </div>

                        <input type="hidden" name="set_location_fullname" id="set_location_fullname" value="<?= $vars['location-fullname'] ?? '' ?>"/>
                        <input type="hidden" name="set_location_name" id="set_location_name" value="<?= $vars['location-name'] ?? '' ?>"/>
                        <input type="hidden" name="set_location_geoname_id" id="set_location_geoname_id" value="<?= $vars['set_location_geoname_id'] ?? '' ?>"/>
                        <input type="hidden" name="set_location_latitude" id="set_location_latitude" value="<?= $vars['set_location_latitude'] ?? '' ?>"/>
                        <input type="hidden" name="set_location_longitude" id="set_location_longitude" value="<?= $vars['set_location_longitude'] ?? '' ?>"/>
                        <input type="hidden" id="original_latitude" value="<?= $vars['set_location_atitude'] ?? '' ?>">
                        <input type="hidden" id="original_longitude" value="<?= $vars['location-longitude'] ?? '' ?>">
                        <div class="invalid-feedback"><?= $words->get('SignupErrorProvideLocation'); ?></div>
                        <small class="text-muted text-justify"><?= $words->get('subline_location') ?></small>
                        <div class="w-100">
                            <div id="map" class="signupmap mb-1"></div>
                        </div>
                    </div>

                    <button type="submit" class="o-input btn btn-primary"><?php echo $words->getSilent('NextStep'); ?> <i class="fa fa-angle-double-right"></i></button>
                    <?php echo $words->flushBuffer(); ?>

                </div>

            </div>
        </form>
    </div>
</div>
