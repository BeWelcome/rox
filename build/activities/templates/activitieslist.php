<?php
if ($this->allActivities != null && sizeof ($this->allActivities) > 0){

    $map_conf = PVars::getObj('map');

    $env_conf = PVars::getObj('env');

    echo '<input type="hidden" id="osm-tiles-provider-base-url" value="' . $map_conf->osm_tiles_provider_base_url . '"/>';
    echo '<input type="hidden" id="osm-tiles-provider-api-key" value="' . $map_conf->osm_tiles_provider_api_key . '"/>';

    // activities map container
    echo '<div id="activities-map"></div>';

    // activities map data
    echo '<div id="activities-data">';
    
    $latitudeMin = null;
    $latitudeMax = null;
    $longitudeMin = null;
    $longitudeMax = null;

    // activities data is stored in a hidden table in order to retrieve it from activities_map.js script
    echo '<table>';
    foreach($this->allActivities as $activity) {
        $location = $activity->location;

        if ($location != null && $location->latitude != null && $location->longitude != null){
            echo '<tr>';
            
            // activity title
            echo '<td>' . $activity->title . '</td>';
            
            // location name
            echo '<td>' . $location->name . '</td>';
            // location latitude
            echo '<td>' . $location->latitude . '</td>';
            // location longitude
            echo '<td>' . $location->longitude . '</td>';
            
            // activity details link URL
            echo '<td>' . $env_conf->baseuri . 'activities/' . $activity->id . '</td>';
            
            // date start
            echo '<td>' . $activity->dateStart . '</td>';
            
            // address
            echo '<td>' . $activity->address . '</td>';
            
            echo '</tr>';

            // update the bounds of the map with this point
            if ($latitudeMin === null || $latitudeMin > $location->latitude){
                $latitudeMin = $location->latitude;
            }

            if ($latitudeMax === null || $latitudeMax < $location->latitude){
                $latitudeMax = $location->latitude;
            }

            if ($longitudeMin === null || $longitudeMin > $location->longitude){
                $longitudeMin = $location->longitude;
            }

            if ($longitudeMax === null || $longitudeMax < $location->longitude){
                $longitudeMax = $location->longitude;
            }
        }

    }
    echo '</table>';
    
    if ($latitudeMin != null){
        // at least one point with valid location
        
        // min & max latitude
        echo '<input type="hidden" id="activity-data-min-latitude" value="' . $latitudeMin . '" />';
        echo '<input type="hidden" id="activity-data-max-latitude" value="' . $latitudeMax . '" />';
        // min & max longitude
        echo '<input type="hidden" id="activity-data-min-longitude" value="' . $longitudeMin . '" />';
        echo '<input type="hidden" id="activity-data-max-longitude" value="' . $longitudeMax . '" />';
    }

    echo '</div>';

}
$this->pager->render(); ?>

<?php

foreach($this->activities as $activity) {
    ?>
    <div class="d-flex flex-row justify-content-start align-items-center p-2 w-100">

        <div class="text-truncate w-100">
            <h4 class="m-0 p-0 w-100 text-truncate"><?php echo '<a href="activities/' . $activity->id . '">' . htmlspecialchars($activity->title) . '</a>'; ?>

                <?php
                echo '<p class="p-0 m-0 smaller text-black-50">' . date("d M Y", strtotime($activity->dateStart));
                if ($activity->dateStart != $activity->dateEnd){
                    echo ' - ' . date("d M Y", strtotime($activity->dateEnd));
                }
                echo '</p>'; ?>
            </h4>

            <?php
            if ($activity->status == 0) {

                $activityInTheFuture = (time()-24*60*60 < strtotime($activity->dateTimeEnd));
                if ($this->member && in_array($this->member->id, array_keys($activity->organizers))
                    && $activityInTheFuture ) {
                    echo '<a href="activities/' . $activity->id . '/edit" class="btn btn-sm btn-primary float-right mt-2 mr-md-2">' . $words->getBuffered('ActivityEdit') . '</a>' . $words->flushBuffer();
                }
            } else {
                echo '<span class="badge badge-danger float-right mt-2 mr-lg-2"><small>' . $words->getBuffered('ActivityCancelled') . '</small></span>' . $words->flushBuffer();
            }
            ?>

        </div>

        <div class="flex-lg-row d-none d-lg-flex">
            <div class="text-right text-nowrap">
                <?php if ($activity->location != null){
                    $locationName = htmlspecialchars($activity->location->name);
                    if ($activity->location->getCountry() != null){
                        $countryName = htmlspecialchars($activity->location->getCountry()->name);
                    } else {
                        $countryName = '';
                    }
                } else {
                    $locationName = '';
                    $countryName = '';
                }
                echo $locationName . '<br>' . $countryName; ?>
            </div>
            <div class="px-2"><i class="fa fa-25 fa-map-marker-alt"></i></div>
        </div>

        <div class="ml-auto flex-md-row d-none d-md-flex">
            <div><i class="fa fa-25 fa fa-user-circle-o"></i></div>
            <div class="attendees">
                        <?php
                        echo '<p class="p-0 m-0 pl-2';
                        if ($activity->attendeesYes == 0){ echo ' invisible'; }
                        echo '">' . $activity->attendeesYes . '&nbsp;' . $words->get('ActivitiesNumbAttendeesYes') . '</p>';
                        echo '<p class="p-0 m-0 pl-2';
                        if ($activity->attendeesMaybe == 0){ echo ' invisible'; }
                        echo '">' . $activity->attendeesMaybe . '&nbsp;' . $words->get('ActivitiesNumbAttendeesMaybe'); ?>
                    </p>
            </div>
        </div>
    </div>
<?php } ?>

<?php $this->pager->render(); ?>
