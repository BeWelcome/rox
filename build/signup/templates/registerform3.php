<?php

$map_conf = PVars::getObj('map');
echo print_r($vars, true);
?>
<input type="hidden" id="osm-tiles-provider-base-url"
       value="<?php echo($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key"
       value="<?php echo($map_conf->osm_tiles_provider_api_key); ?>"/>

    <div class="card card-block w-100">
        <form method="post" action="<?php echo $baseuri . 'signup/4' ?>" class="form" name="geo-form-js" id="geo-form-js">
            <?= $callback_tag ?>

            <div class="d-flex flex-row">
                <div class="d-block mr-3 pr-3">

                    <h4 class="text-center mb-2">Step 3/4</h4>

                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"><span class="white">75%</span></div>
                    </div>

                    <div class="h4 text-center d-none d-md-block mt-1">
                        <div class="my-3"><i class="fa fa-user"></i><br><?php echo $words->get('LoginInformation'); ?></div>
                        <div class="my-3"><i class="fa fa-tag"></i><br><?php echo $words->get('SignupName'); ?></div>
                        <div class="my-3"><i class="fa fa-map-marker"></i><br><?php echo $words->get('Location'); ?></div>
                        <div class="my-3 text-muted"><i class="fa fa-check-square"></i><br><?php echo $words->get('SignupSummary'); ?></div>
                    </div>

                </div>

                <div class="d-block w-100">

                    <span class="text-muted text-justify"><?= $words->get('subline_location') ?></span>
                    <label for="location" class="form-control-label sr-only"><?= $words->getSilent('label_setlocation') ?></label>
                    <?php echo $words->flushBuffer(); ?>
                    <input type="hidden" name="location-geoname-id" id="location-geoname-id" value="<?= isset($vars['location-geoname-id']) ? $vars['location-geoname-id'] : '' ?>"/>
                    <input type="hidden" name="location-latitude" id="location-latitude" value="<?= isset($vars['location-latitude']) ? $vars['location-latitude'] : '' ?>"/>
                    <input type="hidden" name="location-longitude" id="location-longitude" value="<?= isset($vars['location-longitude']) ? $vars['location-longitude'] : '' ?>"/>
                    <input type="text" name="location" id="location" class="form-control location-picker" placeholder="<?= $words->get('label_setlocation') ?>"
                        <?php
                        echo isset($vars['location']) ? 'value="'. htmlentities($vars['location'],ENT_COMPAT, 'utf-8') . '" ' : '';
                        ?>
                    >
                    <div class="form-group">
                        <div id="map" class="mb-1" style="width: 100%; height: 440px; border: 1px solid #aaa;"></div>
                    </div>

                    <button type="submit" class="form-control btn btn-primary"><?php echo $words->getSilent('NextStep'); ?> <i class="fa fa-angle-double-right"></i></button>
                    <?php echo $words->flushBuffer(); ?>

                </div>

            </div>
        </form>
    </div>
</div>