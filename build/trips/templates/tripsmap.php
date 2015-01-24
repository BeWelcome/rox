<?php
    $map_conf = PVars::getObj('map');
    $env_conf = PVars::getObj('env');
    ?>

    <input type="hidden" id="osm-tiles-provider-base-url" value="<?= $map_conf->osm_tiles_provider_base_url ?>">
    <input type="hidden" id="osm-tiles-provider-api-key" value="<?= $map_conf->osm_tiles_provider_api_key ?>">

    <div id="trips-map"></div>

    <div id="trips-data">
        <table>
            <?php
            // trips data is stored in a hidden table in order to retrieve it from activities_map.js script
            if ($this->allTrips) {
                foreach ($this->allTrips as $trip) { ?>
                    <tr>
                        <td><?= $trip->trip_name ?></td>
                        <td><?= $trip->username ?></td>
                        <td><?= $trip->tripstartDate ?></td>
                        <td><?= $trip->tripendDate ?></td>
                        <td><?= $trip->latitude ?></td>
                        <td><?= $trip->longitude ?></td>
                        <td><?= $env_conf->baseuri . 'trips/' . $trip->trip_id ?></td>
                    </tr><?php
                }
            }
            ?>
        </table>
        <?php
        if ($latitudeMin != null) {
            // at least one point with valid trip
            ?>
            <input type="hidden" id="trip-data-min-latitude" value="<?= $latitudeMin ?>" >
            <input type="hidden" id="trip-data-max-latitude" value="<?= $latitudeMax ?>" >
            <input type="hidden" id="trip-data-min-longitude" value="<?= $longitudeMin ?>" >
            <input type="hidden" id="trip-data-max-longitude" value="<?= $longitudeMax ?>" >
        <?php
        } ?>
    </div>
