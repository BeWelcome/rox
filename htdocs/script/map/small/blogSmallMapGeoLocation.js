var mapBuilder;

var firstChange = true;

function initOsmMapBlogEdit() {
  if (jQuery('#spaf_map').length > 0){

    var cloudmadeApiKey = jQuery('#cloudmadeApiKeyInput').val();

    if (cloudmadeApiKey == null || cloudmadeApiKey == ''){
      bwrox.error('CloudMade API key not defined!');
    }

    mapBuilder = new BWSimpleMapBuilder(cloudmadeApiKey, "spaf_map", false);

    var markerLatitude = jQuery('#markerLatitude').val();
    var markerLongitude = jQuery('#markerLongitude').val();
    if (markerLatitude != null && markerLongitude != null) {
            // zoom map to specified location
            if (markerLatitude == "0" && markerLongitude == "0") {
                var zoomLevel = 1;
            } else {
                var zoomLevel = 8;
            }
      mapBuilder.setCenter(markerLatitude, markerLongitude, zoomLevel);

      var markerDescription = jQuery('#markerDescription').val();
      if (markerDescription != null) {
        // add marker
        mapBuilder.addSimpleMarker(markerLatitude, markerLongitude, markerDescription);;
      }
    }
  }
}

function removeHighlight() {
    var lis = $A($('locations').childNodes);
    lis.each(function(li) {
        Element.setStyle(li, {
            fontWeight: '',
            backgroundColor: '',
            backgroundImage: ''
        });
    });
}

/**
 * called when an user click on a result of the list, to update the marker.
 * */
function setMap(geonameid, latitude, longitude, zoom, geonamename, countryname, countrycode, admincode) {
    setGeonameIdInFormBlog(geonameid, latitude, longitude, geonamename, countryname, countrycode, admincode);
    changeMarkerBlog(latitude, longitude, zoom, decodeURIComponent(geonamename) + ', ' + decodeURIComponent(countryname));
    removeHighlight();
    Element.setStyle($('li_'+geonameid), {
        fontWeight: 'bold',
        backgroundColor: '#ffffff',
        backgroundImage: 'url(images/icons/tick.png)'
    });
}

function changeMarkerBlog(markerLatitude, markerLongitude, zoomLevel, markerDescription) {
  // refresh if necessary
  if (firstChange){
    jQuery('#spaf_map').show();
    mapBuilder.refresh();
    firstChange = false;
  }
  mapBuilder.clearMap();
  mapBuilder.setCenter(markerLatitude, markerLongitude, zoomLevel);
  if (markerDescription != null) {
    // add marker
    mapBuilder.addSimpleMarker(markerLatitude, markerLongitude, markerDescription);;
  }
}

function setGeonameIdInFormBlog(geonameid, latitude, longitude, geonamename, countrycode, admincode) {
    jQuery('#geonameid').val(geonameid);
    jQuery('#latitude').val(latitude);
    jQuery('#longitude').val(longitude);
    jQuery('#geonamename').val(geonamename);
    jQuery('#geonamecountrycode').val(countrycode);
    jQuery('#admincode').val(admincode);
}
