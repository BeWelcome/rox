<h3 id="trip_map"><?php echo $words->get('TripMap'); ?></h3>

<div id="map_<?php echo $trip->trip_id; ?>" style="width: 350px; height: 250px;"></div>

<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

?>" type="text/javascript"></script>
<script type="text/javascript" src="script/labeled_marker.js"></script>
<script type="text/javascript">
var map_<?php echo $trip->trip_id; ?> = null;
var points;
<?php
foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
	echo 'var latlang_'.$blogid.';';
}

?>

function load_map() {
	if (GBrowserIsCompatible()) {
		map_<?php echo $trip->trip_id; ?> = new GMap2($('map_<?php echo $trip->trip_id; ?>'));
		map_<?php echo $trip->trip_id; ?>.addControl(new GSmallMapControl());
		map_<?php echo $trip->trip_id; ?>.addControl(new GMapTypeControl());
<?php

	$first = true;
	$i = 0;
	echo 'points = new Array(); ';
	foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
		if ($blog->latitude && $blog->longitude) {
			if ($first) {
				echo 'map_'.$trip->trip_id.'.setCenter(new GLatLng('.$blog->latitude.', '.$blog->longitude.'), 8);';
				$first = false;
			}
			echo 'latlang_'.$blogid.' = new GLatLng('.$blog->latitude.', '.$blog->longitude.');';
			echo 'points['.$i++.'] = '.$blogid.';';
            // track the current result number
            echo 'var opts_'.$blogid.' = {
                "icon": icon,
                "clickable": true,
                "labelText": '.$i.',
                "labelOffset": new GSize(-5, -29)
            };';
			echo 'var marker'.$blogid.' = new LabeledMarker(latlang_'.$blogid.', opts_'.$blogid.');';
			echo 'map_'.$trip->trip_id.'.addOverlay(marker'.$blogid.');';
			echo 'GEvent.addListener(marker'.$blogid.', "click", function() {
					marker'.$blogid.'.openInfoWindowHtml("<a href=\"blog/'.$trip->handle.'/'.$blogid.'\">'.$blog->blog_title.'</a><br />'.$blog->name.'<br />'.$blog->blog_start.'");
				});';
		}
	}

?>
        map_<?php echo $trip->trip_id; ?>.addMapType(G_PHYSICAL_MAP);
        map_<?php echo $trip->trip_id; ?>.setMapType(G_PHYSICAL_MAP);
		setPolyline();
	}
	
	
}
var polyline;
	function setPolyline() {
		if (points) {
			var polypoints = new Array();
			var i = 0;
			points.each(function(value, index) {
				latlang = eval('latlang_'+value)
				if (latlang) {
					polypoints[i] = latlang;
					i++;
				}
			});
			if (polyline) {
				map_<?php echo $trip->trip_id; ?>.removeOverlay(polyline);
			}
			polyline = new GPolyline(polypoints, "#000", 5);
			map_<?php echo $trip->trip_id; ?>.addOverlay(polyline);
				
		}
	}

var icon = new GIcon(); // green - agreeing
icon.image = "images/icons/gicon_flag.png";
icon.shadow = "images/icons/gicon_flag_shadow.png";
icon.iconSize = new GSize(29, 21);
icon.shadowSize = new GSize(29, 21);
icon.iconAnchor = new GPoint(1, 21);
icon.infoWindowAnchor = new GPoint(1, 21);

function loadMaps() {
	load_map();
}
window.onload = loadMaps;
window.onunload = GUnload;
</script>