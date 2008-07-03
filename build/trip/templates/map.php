<?php

/*foreach($trips as $trip) {
	require TEMPLATE_DIR.'apps/trip/tripitem.php';
}*/

?>

<div class="popupmap" id="map_alltrips">
    <div id="map" style="width:100%; height:250px;"></div>
</div>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php
    $google_conf = PVars::getObj('config_google');
    if (!$google_conf || !$google_conf->maps_api_key) {
        throw new PException('Google config error!');
    }
    echo $google_conf->maps_api_key;

?>" type="text/javascript"></script>
<script type="text/javascript" src="script/marker_manager.js"></script>   
<script type="text/javascript">
//<![CDATA[
/*function displayMap(popupid, lng, ltd, desc) {
//    Element.setStyle(popupid, {display:'block'});
    if (GBrowserIsCompatible()) {
        map = new GMap2(document.getElementById("map"));
            map.addControl(new GLargeMapControl());
            map.addControl(new GHierarchicalMapTypeControl());
            map.enableDoubleClickZoom();
            map.setCenter(new GLatLng(15, 10), 2);
            map.addMapType(G_PHYSICAL_MAP);
            map.setMapType(G_PHYSICAL_MAP);    
        }
}*/

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
    "zoom": [0, 3],
    "places": [
    <?php
    $counter = 0;
    foreach($trips as $trip) { 
        if (isset($trip_data[$trip->trip_id])) {
            foreach ($trip_data[$trip->trip_id] as $blogid => $blog) {
                if ($blog->latitude && $blog->longitude) {
                    echo '
                      {
                        "name": "'.$trip->trip_name.'",
                        "icon": ["gicon_flag", "gicon_flag_shadow"],
                        "posn": ['.$blog->latitude.', '.$blog->longitude.']
                      },';
                }
                if(++ $counter >= 1 ) break;
            }
        }
    } ?>
      {
        "name": "Canadian Offices",
        "icon": ["gicon_flag", "gicon_flag_shadow"],
        "posn": [58, -101]
      }
    ]
  },
 {
    "zoom": [4, 6],
    "places": [
      {
        "name": "Headquarters",
        "icon": ["headquarters", "headquarters-shadow"],
        "posn": [37.423021, -122.083739]
      },
      {
        "name": "New York Sales & Engineering Office",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [40.754606, -73.986794]
      },
      {
        "name": "Atlanta Sales &amp; Engineering Office",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [33.781506, -84.387422]
      },
      {
        "name": "Dallas Sales Office",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [36.4724385, -101.044637]
      },
      {
        "name": "Cambridge Sales & Engineering Office",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [42.362331, -71.083661]
      },
      {
        "name": "Chicago Sales Office",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [41.889232, -87.628767]
      },
      {
        "name": "Denver & Boulder Offices",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [39.563011, -104.868962]
      },
      {
        "name": "Detroit Sales Office",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [42.475482, -83.244587]
      },
      {
        "name": "Santa Monica & Irvine Offices",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [33.715585, -118.177435]
      },
      {
        "name": "Phoenix Sales & Engineering Office",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [33.411782, -111.926247]
      },
      {
        "name": "Pittsburgh Engineering Office",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [40.444541, -79.946254]
      },
      {
        "name": "Seattle Engineering & Sales Offices",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [47.664261, -122.274308]
      },
      {
        "name": "Canada Sales Office",
        "icon": ["gicon_flag", "gicon_flag-shadow"],
        "posn": [43.645478, -79.378843]
      },
    ]
  },
  {
    "zoom": [7, 17],
    "places": [
      {
        "name": "Headquarters",
        "posn": [37.423021, -122.083739]
      },
      {
        "name": "New York Sales & Engineering Office",
        "posn": [40.754606, -73.986794]
      },
      {
        "name": "Atlanta Sales &amp; Engineering Office",
        "posn": [33.781506, -84.387422]
      },
      {
        "name": "Boulder Sales & Engineering Office",
        "posn": [40.018520, -105.276882]
      },
      {
        "name": "Cambridge Sales & Engineering Office",
        "posn": [42.362331, -71.083661]
      },
      {
        "name": "Chicago Sales Office",
        "posn": [41.889232, -87.628767]
      },
      {
        "name": "Dallas Sales Office",
        "posn": [32.925355, -96.816087]
      },
      {
        "name": "Denver Sales Office",
        "posn": [39.563011, -104.868962]
      },
      {
        "name": "Detroit Sales Office",
        "posn": [42.475482, -83.244587]
      },
      {
        "name": "Irvine Sales & Engineering Office",
        "posn": [33.660021, -117.860142]
      },
      {
        "name": "Phoenix Sales & Engineering Office",
        "posn": [33.411782, -111.926247]
      },
      {
        "name": "Pittsburgh Engineering Office",
        "posn": [40.444541, -79.946254]
      },
      {
        "name": "Santa Monica Sales & Engineering Office",
        "posn": [34.019388, -118.494728]
      },
      {
        "name": "Seattle Engineering Office",
        "posn": [47.678415, -122.195713]
      },
      {
        "name": "Seattle Sales Office",
        "posn": [47.650106, -122.352903]
      },
      {
        "name": "Toronto Sales Office",
        "posn": [43.645478, -79.378843]
      },
    ]
  }
];

</script>
<script type="text/javascript" src="script/trip_functions.js"></script>
<script type="text/javascript">
//window.onload = displayMap;
window.onload = load();
window.onunload = GUnload;
    //]]>
</script>
