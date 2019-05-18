<?php

$map_conf = PVars::getObj('map');
?>
<input type="hidden" id="osm-tiles-provider-base-url"
       value="<?php echo($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key"
       value="<?php echo($map_conf->osm_tiles_provider_api_key); ?>"/>

    <div class="card card-block w-100">
        <form method="post" class="form" name="geo-form-js" id="geo-form-js">
            <?= $callback_tag ?>

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

                    <label for="location" class="form-control-label sr-only"><?= $words->getSilent('label_setlocation') ?></label>
                    <?php echo $words->flushBuffer(); ?>
                    <input type="hidden" name="location-geoname-id" id="location-geoname-id" value="<?= isset($vars['location-geoname-id']) ? $vars['location-geoname-id'] : '' ?>"/>
                    <input type="hidden" name="location-latitude" id="location-latitude" value="<?= isset($vars['location-latitude']) ? $vars['location-latitude'] : '' ?>"/>
                    <input type="hidden" name="location-longitude" id="location-longitude" value="<?= isset($vars['location-longitude']) ? $vars['location-longitude'] : '' ?>"/>
                    <input type="text" name="location" id="location" oninput="RemoveOverlay();" class="form-control location-picker" placeholder="<?= $words->get('label_setlocation') ?>"
                        <?php
                        echo isset($vars['location']) ? 'value="'. htmlentities($vars['location'],ENT_COMPAT, 'utf-8') . '" ' : '';
                        ?>
                    >
                    <script>
                        function RemoveOverlay() {
                            document.getElementById("mapoverlay").style.display = "none";
                        }
                    </script>
                    <div class="form-group">
                        <div id="mapoverlay">
                            <span class="text-muted text-justify"><?= $words->get('subline_location') ?></span>
                        </div>
                        <div class="w-100">
                            <div id="map" class="signupmap mb-1"></div>
                        </div>
                    </div>

                    <button type="submit" class="form-control btn btn-primary"><?php echo $words->getSilent('NextStep'); ?> <i class="fa fa-angle-double-right"></i></button>
                    <?php echo $words->flushBuffer(); ?>

                </div>

            </div>
        </form>
    </div>
</div>