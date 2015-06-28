<?php
    $map_conf = PVars::getObj('map');
    $env_conf = PVars::getObj('env');
    ?>

    <input type="hidden" id="osm-tiles-provider-base-url" value="<?= $map_conf->osm_tiles_provider_base_url ?>">
    <input type="hidden" id="osm-tiles-provider-api-key" value="<?= $map_conf->osm_tiles_provider_api_key ?>">

    <div id="trips-map" class="map"><div id="progress"><div id="progress-label"><?= $words->getBuffered('TripsLoadingMapTrips') ?></div></div></div>
