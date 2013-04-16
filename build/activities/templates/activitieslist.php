<?php
if ($this->allActivities != null && sizeof ($this->allActivities) > 0){

    // retrieve cloudmade API key
    $cloudmade_conf = PVars::getObj('cloudmade');

    $env_conf = PVars::getObj('env');


    echo '<input type="hidden" id="cloudmade-api-key-input" value="' . $cloudmade_conf->cloudmade_api_key . '"/>';

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


<table class='activitieslist'>
<?php
$count= 0;
foreach($this->activities as $activity) {
    echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '">';
    echo '<td style="padding-bottom: 30px; width: 10%;">
            <div class="calendar calendar-icon-' . date("m", strtotime($activity->dateStart)) . '">
              <div class="calendar-day">' . date("j", strtotime($activity->dateStart)) . '</div>
              <div class="calendar-year">' . date("Y", strtotime($activity->dateStart)) . '</div></td>';
    echo '<td colspan="2"><div class="small grey">' . $activity->dateStart . '-' . $activity->dateEnd . '</div><h3><a href="activities/' . $activity->id . '">' . $activity->title . '</a><h3></td>';
    echo '<td><i class="icon-map-marker icon-3x grey float_right"></i></td>';
    if ($activity->location != null){
        $locationName = $activity->location->name;
        if ($activity->location->getCountry() != null){
            $countryName = $activity->location->getCountry()->name;
        }else{
            $countryName = '';
        }
    }else{
        $locationName = '';
        $countryName = '';
    }
    echo '<td>' . $locationName . '<br /> ' . $countryName . '</td>';
    echo '<td>' . count($activity->attendees) . '&nbsp;' . $words->get('ActivitiesNumbAttendees') . '</td>';
    echo '<td width="112px"><div class="small grey">' . $words->get('ActivitiesOrganizedBy') . '</div>';
    $organizers = '';
    foreach($activity->organizers as $organizer) {
        $organizers .= MOD_layoutbits::PIC_40_40($organizer->Username,'',$style='framed float_left') . " ";
    }
    echo substr($organizers, 0, -1) . '</td>';
    if ($this->member && in_array($this->member->id, array_keys($activity->organizers))) {
        echo '<td><a href="activities/' . $activity->id . '/edit">'
        . '<img src="images/icons/comment_edit.png" alt="edit" /></a></td>';
    } else {echo '<td></td>';}
    echo '</tr>';
    $count++;
}
?>
</table>
<?php $this->pager->render(); ?>
