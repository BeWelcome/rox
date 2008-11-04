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
    <form method="get" action="trip/search">
    <h1 id="trip_name">
        <a href="trip">
        <?php echo $words->getFormatted('tripsTitle'); ?><?=$sub ? ' / ' : ''?> 
        </a>
        <?=$sub?>
    </h1>
    <div class="trip_author" style="padding: 10px 10px 8px 10px"><a href="trip/search"><?=$words->get('TripsSearch')?> </a>
        <input type="text" style="font-size: 12px" name="s" onfocus="this.value='';" value="<?=$words->getSilent('TripsSearchEnterLocation')?>">
    </div>
    </form>
</div>
<div class="popupmap" id="map_alltrips">
    <div id="map" style="width:100%; height:250px; border-top: 2px solid #666; background-color: #333;"></div>
</div>
<div id="handle2" style="width: 100%; height: 14px; cursor: s-resize; text-align: center"><a href="#" onclick="return false" title="Drag this bar to resize the map!"><img src="images/btns/resize_hor.png"></a></div>

<?=$words->flushBuffer()?>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

?>" type="text/javascript"></script>
<script type="text/javascript" src="script/marker_manager.js"></script>   
<script type="text/javascript" src="script/resizable.js"></script>  
<script type="text/javascript" src="script/trip_functions.js"></script>
<script type="text/javascript">
//<![CDATA[
/*
function displayMap(popupid, lng, ltd, desc) {
//    Element.setStyle(popupid, {display:'block'});
    if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
            var mapTypeControl = new GSmallMapControl();
            var topRight = new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(30,50));
    		map.addControl(mapTypeControl,topRight);
    		map.addControl(new GMapTypeControl());
            map.enableDoubleClickZoom();
            map.setCenter(new GLatLng(15, 10), 2);
            map.addMapType(G_PHYSICAL_MAP);
            map.setMapType(G_PHYSICAL_MAP);    
        }
}
*/

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

var officeLayer = [
  {
    "zoom": [0, 17],
    "places": [
    <?php
    $point = 0;
    $counter = 0;
    $locations = array();
    foreach($trips as $trip) { 
        if (isset($trip_data[$trip->trip_id])) {
            foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
                if ($blog->latitude && $blog->longitude) {
                    ++$point;
                    echo '
                      {
                        "name": "'.$blog->name.'",
                        "icon": ["gicon_flag", "gicon_flag_shadow"],
                        "posn": ['.$blog->latitude.', '.$blog->longitude.']
                      },';
                      $locations[$point]->lat = $blog->latitude;
                      $locations[$point]->lng = $blog->longitude;
                      $locations[$point]->name = $blog->name;
                      $locations[$point]->trip_id = $trip->trip_id;
                }
            }
        }
        if(++ $counter == 40 ) {
?>
    ]
  },
 {
    "zoom": [4, 17],
    "places": [
<?php
        } elseif ($counter == 200) {
?>
    ]
  },
 {
    "zoom": [7, 17],
    "places": [
<?php
        }
        if(++ $counter >= 1000 ) break;
    } ?>
    ]
  }
];

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

    var map;
    var mgr;
    <?php
    $point = rand(1,count($locations));
    ?>
    function load() {
      if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
        var mapTypeControl = new GSmallMapControl();
        var topRight = new GControlPosition(G_ANCHOR_TOP_RIGHT, new GSize(30,50));
        map.addControl(mapTypeControl,topRight);
        map.addControl(new GMapTypeControl());
        map.setCenter(new GLatLng(<?=$locations[$point]->lat?>, <?=$locations[$point]->lng?>), 5);
        map.addMapType(G_PHYSICAL_MAP);
        map.setMapType(G_PHYSICAL_MAP); 
        map.enableDoubleClickZoom();
        mgr = new MarkerManager(map, {trackMarkers:true});
        window.setTimeout(setupOfficeMarkers, 0);
        // zoomfit(map);

      }
    }

/*
* Starts the loading of the Google Maps API, once loaded will call the specified callback Function
*/
function activateMap() {
	var script = document.createElement("script");
	script.setAttribute("src", "http://maps.google.com/maps?file=api&v=2&key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

?>&async=2&callback=load");
	script.setAttribute("type", "text/javascript");
	document.documentElement.firstChild.appendChild(script);
    
}

//window.onload = displayMap;
window.onload = load;
// window.onunload = GUnload;
$('handle2').onmouseover = HighlightUp;
$('handle2').onmouseout = HighlightDown;
new Resizable('map', {minWidth:0, minHeight:200, handle:'handle2', constraint:'vertical'});
    //]]>
</script>
