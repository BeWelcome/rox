<?php $map_conf = PVars::getObj('map'); ?>
<input type="hidden" id="osm-tiles-provider-base-url" value="<?php echo ($map_conf->osm_tiles_provider_base_url); ?>"/>
<input type="hidden" id="osm-tiles-provider-api-key" value="<?php echo ($map_conf->osm_tiles_provider_api_key); ?>"/>
<?php
$words = new MOD_words();
$request = PRequest::get()->request;
$sub = '';
if (isset($request[1]) && $request[1] == 'show' && isset($request[2])) {
    $sub = '<a href="trip/show/'.$request[2].'" style="padding-right: 10px;">';
    $sub .= ($request[2]=='my') ? $words->get('TripsShowMy') : $words->get('TripsUserTrips',$request[2]);
    $sub .= '</a>';
}
?>
<div id="onmap">
    <h1 id="trip_name">
        <a href="trip">
        <?php echo $words->getFormatted('tripsTitle'); ?><?=$sub ? ' / ' : ''?> 
        </a>
        <?=$sub?>
    </h1>
</div>
<div class="popupmap" id="map_alltrips">
    <div id="tripMap" class="tripmap"></div>
</div>
<div id="handle2" style="width: 100%; height: 14px; cursor: s-resize; text-align: center"><a href="#" onclick="return false" title="Drag this bar to resize the map!"><img src="images/btns/resize_hor.png" alt="resize" /></a></div>

<?=$words->flushBuffer()?>

<!-- <script type="text/javascript" src="script/marker_manager.js"></script>    -->
<script type="text/javascript" src="script/resizable.js"></script>  
<!-- <script type="text/javascript" src="script/trip_functions.js"></script> -->
<script type="text/javascript">
//<![CDATA[

<?php // dynamically create addMarkers javascript function ?>
        
var markers = new Array();
<?php
$zoomLevel = 4;    
$point = 0;
    $locations = array();
    foreach($trips as $trip) { 
        if (isset($trip_data[$trip->trip_id])) {
            foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
                if ($blog->latitude && $blog->longitude) {
                    ++$point;
                    echo 'markers['.($point-1).']={latitude:'.$blog->latitude
                    			.', longitude:'.$blog->longitude
                    			.', name:"'.$blog->name.'"'
                    			.', tripId:'.$trip->trip_id.'};';
                    $locations[$point] = new stdClass();
                    $locations[$point]->lat = $blog->latitude;
                    $locations[$point]->lng = $blog->longitude;
                    $locations[$point]->name = $blog->name;
                    $locations[$point]->trip_id = $trip->trip_id;
                }
            }
        }
        if($point == 40 ) {
        	$zoomLevel = 7;
        } elseif ($point == 200) {
        	$zoomLevel = 2;
        } elseif ($point >= 1000 ) {
        	// do not display more that 1000 points
        	break;
        }
    }
?>
bwrox.debug('%d markers defined.', markers.length);

//Markers
var iconData = {
  "gicon_flag": { width: 29, height: 21 },
  "ca": { width: 24, height: 14 },
  "gicon_flag_shadow": { width: 29, height: 21 },
  "house": { width: 32, height: 32 },
  "house-shadow": { width: 59, height: 32 },
  "headquarters": { width: 32, height: 32 },
  "headquarters-shadow": { width: 59, height: 32 }
};

function HighlightUp() {
    new Effect.Highlight(this, {startcolor: '#333333',endcolor: '#666666',restorecolor: '#666666',duration: .5});
}
function HighlightDown() {
    new Effect.Highlight(this, {startcolor: '#666666',endcolor: '#333333',restorecolor: '#333333',duration: .5});
}

function startRest() {
	var markermanager = document.createElement("markermanager");
	markermanager.setAttribute("src", "script/marker_manager.js");
	markermanager.setAttribute("type", "text/javascript");
	var resizable = document.createElement("resizable");
	resizable.setAttribute("src", "script/resizable.js");
	resizable.setAttribute("type", "text/javascript");
	var tripfunctions = document.createElement("tripfunctions");
	tripfunctions.setAttribute("src", "script/trip_functions.js");
	tripfunctions.setAttribute("type", "text/javascript");
    
	document.documentElement.firstChild.appendChild(markermanager);
	document.documentElement.firstChild.appendChild(resizable);
	document.documentElement.firstChild.appendChild(tripfunctions);
}

$('handle2').onmouseover = HighlightUp;
$('handle2').onmouseout = HighlightDown;
new Resizable('tripMap', {minWidth:0, minHeight:200, handle:'handle2', constraint:'vertical'});
    //]]>
</script>

<?php 
	// random point used to center the map 
	$point = rand(1,count($locations));
	
	if ($locations != null && $point != null && $locations[$point] != null){
		$lat = $locations[$point]->lat;
		$lng = $locations[$point]->lng;
	}else{
		// arbitrary center to London
		$lat = '51.505';
		$lng = '-0.09';
	}
	
	echo '<input type="hidden" id="centerLatitude" name="centerLatitude" value="' . $lat . '"/>';
	echo '<input type="hidden" id="centerLongitude" name="centerLongitude" value="' . $lng . '"/>';
	echo '<input type="hidden" id="zoomLevel" name="zoomLevel" value="'.$zoomLevel.'"/>';
?>