<?php
$words = new MOD_words();
$styleadd = '';
if (strlen($trip->trip_name) >= 20) $styleadd = 'font-size: 22px';
if (!$trip_data) $trip_data[$trip->trip_id] = false;
?>
<div id="onmap">
    <h3 id="trip_name" style="<?=$styleadd?>">
        <a href="trip/<?=$trip->trip_id ?>" style="padding-right: 10px;">
        <?=$trip->trip_name ?>
        </a>
    </h3>

    <div class="trip_author"><?=$words->get('by')?> <a href="members/<?php echo $trip->handle; ?>"><?php echo $trip->handle; ?></a>
        <a href="blog/<?php echo $trip->handle; ?>" title="Read blog by <?php echo $trip->handle; ?>"><img src="images/icons/blog.gif" alt="" /></a>
        <a href="trip/show/<?php echo $trip->handle; ?>" title="Show trips by <?php echo $trip->handle; ?>"><img src="images/icons/world.gif" alt="" /></a>
    </div>
</div>
<!--<h3 id="trip_map"><?php echo $words->get('TripMap'); ?></h3> -->
<!--<div id="map_corner_left">
</div>
<div id="map_corner_bottom" style="width: 100px; height: 100px; background: transparent url(images/misc/col1_replacer2.gif) top left no-repeat; position: relative; top: 100px; margin-bottom: -100px">
</div>
<div id="map_alltrips">
<div id="map_<?php echo $trip->trip_id; ?>" class="tripmap"></div>
</div>
<div id="handle2" style="width: 100%; height: 14px; cursor: s-resize; text-align: center"><a href="#" onclick="return false" title="Drag this bar to resize the map!"><img src="images/btns/resize_hor.png"></a></div>
-->
<script type="text/javascript" src="script/resizable.js"></script>  
<script type="text/javascript">
var map_<?php echo $trip->trip_id; ?> = null;
var points;
<?php
if ($trip_data[$trip->trip_id]) {
foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
	echo 'var latlang_'.$blogid.';';
}
}

?>

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



</script>
