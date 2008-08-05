<?php
$words = new MOD_words();
$styleadd = '';
if (strlen($trip->trip_name) >= 20) $styleadd = 'size: 20px';
?>
<div id="onmap">
    <h1 id="trip_name" style="<?=$styleadd?>">
        <a href="trip">
        <?php echo $words->getFormatted('tripsTitle'); ?> / 
        </a>
        <a href="trip/<?=$trip->trip_id ?>" style="padding-right: 10px;">
        <?=$trip->trip_name ?>
        </a>
    </h1>

    <div class="trip_author"><?=$words->get('by')?> <a href="members/<?php echo $trip->handle; ?>"><?php echo $trip->handle; ?></a>
        <a href="blog/<?php echo $trip->handle; ?>" title="Read blog by <?php echo $trip->handle; ?>"><img src="images/icons/blog.gif" alt="" /></a>
        <a href="trip/show/<?php echo $trip->handle; ?>" title="Show trips by <?php echo $trip->handle; ?>"><img src="images/icons/world.gif" alt="" /></a>
    </div>
</div>
<!--<h3 id="trip_map"><?php echo $words->get('TripMap'); ?></h3> -->
<div id="map_<?php echo $trip->trip_id; ?>" class="trip_map"></div>
<div id="handle2" style="width: 100%; height: 14px; cursor: s-resize; text-align: center"><a href="#" onclick="return false" title="Drag this bar to resize the map!"><img src="images/btns/resize_hor.png"></a></div>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

?>" type="text/javascript"></script>
<script type="text/javascript" src="script/labeled_marker.js"></script>
<script type="text/javascript" src="script/resizable.js"></script>  
<script type="text/javascript" src="script/trip_functions.js"></script>  
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
        var mapTypeControl = new GSmallMapControl();
        var topRight = new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(30,50));
		map_<?php echo $trip->trip_id; ?>.addControl(mapTypeControl,topRight);
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
            echo 'bounds.extend(latlang_'.$blogid.');';
			echo 'points['.$i++.'] = '.$blogid.';';
            // track the current result number
            echo 'var opts_'.$blogid.' = {
                "icon": icon,
                "clickable": true,
                "labelText": "'.$i.' <span>" + decodeURIComponent("'.htmlspecialchars($blog->name).'" + "</span>"),
                "labelOffset": new GSize(-5, -29)
            };';
			echo 'var marker'.$blogid.' = new LabeledMarker(latlang_'.$blogid.', opts_'.$blogid.');';
			echo 'map_'.$trip->trip_id.'.addOverlay(marker'.$blogid.');';
			echo 'GEvent.addListener(marker'.$blogid.', "click", function() {
					marker'.$blogid.'.openInfoWindowHtml("<a href=\"blog/'.$trip->handle.'/'.$blogid.'\">'.$blog->blog_title.'</a><br />'.htmlentities($blog->name).'<br />'.$blog->blog_start.'");
				});';
		}
	}

?>
        map_<?php echo $trip->trip_id; ?>.addMapType(G_PHYSICAL_MAP);
        map_<?php echo $trip->trip_id; ?>.setMapType(G_PHYSICAL_MAP);
		setPolyline();
        zoomfit(map_<?php echo $trip->trip_id; ?>);
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

function HighlightUp() {
    new Effect.Highlight(this, {startcolor: '#333333',endcolor: '#666666',restorecolor: '#666666',duration: .5});
}
function HighlightDown() {
new Effect.Highlight(this, {startcolor: '#666666',endcolor: '#333333',restorecolor: '#333333',duration: .5});
}
$('handle2').onmouseover = HighlightUp;
$('handle2').onmouseout = HighlightDown;

new Resizable('map_<?php echo $trip->trip_id; ?>', {minWidth:0, minHeight:200, handle:'handle2', constraint:'vertical'});

</script>