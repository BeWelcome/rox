<?php $this->pager->render();

if ($this->allActivities != null && sizeof ($this->allActivities) > 0){

	// retrieve cloudmade API key
	$cloudmade_conf = PVars::getObj('cloudmade');

	$env_conf = PVars::getObj('env');
	
	
	echo '<input type="hidden" id="cloudmadeApiKeyInput" value="' . $cloudmade_conf->cloudmade_api_key . '"/>';
	
	// map container
	echo '<div id="activitiesMap"></div>';
	
	// map data
	echo '<div id="activitiesData">';
	
	$latitudeMin = null;
	$latitudeMax = null;
	$longitudeMin = null;
	$longitudeMax = null;
	
	foreach($this->allActivities as $activity) {
		$location = $activity->location;
		
		if ($location != null && $location->latitude != null && $location->longitude != null){
		
			echo '<div class="activityData">';
			echo '<input type="hidden" class="activityTitle" value="' . $activity->title . '" />';
			echo '<input type="hidden" class="locationName" value="' . $location->name . '" />';
			
			echo '<input type="hidden" class="activityUrl" value="' . $env_conf->baseuri . 'activities/' . $activity->id . '" />';
			
			echo '<input type="hidden" class="latitudeValue" value="' . $location->latitude . '" />';
			echo '<input type="hidden" class="longitudeValue" value="' . $location->longitude . '" />';
			echo '</div>';
		
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
	
	echo '<input type="hidden" id="activityDataLatitudeMin" value="' . $latitudeMin . '" />';
	echo '<input type="hidden" id="activityDataLatitudeMax" value="' . $latitudeMax . '" />';
	echo '<input type="hidden" id="activityDataLongitudeMin" value="' . $longitudeMin . '" />';
	echo '<input type="hidden" id="activityDataLongitudeMax" value="' . $longitudeMax . '" />';
	
	$latitudeCenter = $latitudeMax - $latitudeMin;
	$longitudeCenter = $longitudeMax - $longitudeMin;
	
	echo '<input type="hidden" id="activityDataLatitudeCenter" value="' . $latitudeCenter . '" />';
	echo '<input type="hidden" id="activityDataLongitudeCenter" value="' . $longitudeCenter . '" />';
			
	echo '</div>';
	
}
?>

<table class='activitieslist'>
<?php 
$count= 0;
foreach($this->activities as $activity) {
    echo '<tr class="' . $background = (($count % 2) ? 'highlight' : 'blank') . '" title="' . $activity->title . '">';
    echo '<td style="padding-bottom: 30px; width: 10%;">
            <div class="calendar calendar-icon-' . date("m", strtotime($activity->dateStart)) . '">
              <div class="calendar-day">' . date("j", strtotime($activity->dateStart)) . '</div>
              <div class="calendar-year">' . date("Y", strtotime($activity->dateStart)) . '</div></td>';
    echo '<td colspan="2"><div class="small grey">' . $activity->dateStart . '-' . $activity->dateEnd . '</div><h3><a href="activities/' . $activity->id . '">' . $activity->title . '</a><h3></td>';
    echo '<td><i class="icon-map-marker icon-3x grey"></i></td>';
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
    echo substr($organizers, 0, -2) . '</td>';
    if (isset($this->member) && in_array($this->member->id, array_keys($activity->organizers))) {
        echo '<td><a href="activities/' . $activity->id . '/edit">'
        . '<img src="images/icons/comment_edit.png" alt="edit" /></a></td>';
    } else {echo '<td></td>';}
    echo '</tr>';
    $count++;
}
?>
</table>
<?php $this->pager->render(); ?>
