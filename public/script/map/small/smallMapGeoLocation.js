jQuery(function() {
  // document loaded
  if ($('geoselector')){
      $('geoselector').style.display = 'none';
  }
  if ($('geoselector')){
      $('geoselectorjs').style.display = 'block';
  }
  if ($('geoselector')){
      $('spaf_map').style.display = 'block';
  }
  GeoSuggest.initialize('geo-form');
  initOsmMap();
});

var mapBuilder;

function initOsmMap() {

  if (jQuery('#spaf_map').length > 0){

    mapBuilder = new BWSimpleMapBuilder("spaf_map", false);

    var markerLatitude = jQuery('#markerLatitude').val();
    var markerLongitude = jQuery('#markerLongitude').val();
    if (markerLatitude != null && markerLongitude != null) {
            // zoom map to specified location
            if (markerLatitude == "0" && markerLongitude == "0") {
                var zoomLevel = 0;
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
 *
 */
function setMap(geonameId, latitude, longitude, zoom, geonamename, countryname, countrycode, admincode) {
    setGeonameIdInForm(geonameId, latitude, longitude, geonamename, countryname, countrycode, admincode);
    changeMarker(latitude, longitude, zoom, decodeURIComponent(geonamename) + ', ' + decodeURIComponent(countryname));
    removeHighlight();
    Element.setStyle($('li_'+geonameId), {
        fontWeight: 'bold',
        backgroundColor: '#ffffff',
        backgroundImage: 'url(images/icons/tick.png)'
    });
}

function changeMarker(markerLatitude, markerLongitude, zoomLevel, markerDescription) {
  mapBuilder.clearMap();
  mapBuilder.setCenter(markerLatitude, markerLongitude, zoomLevel);
  if (markerDescription != null) {
    // add marker
    mapBuilder.addSimpleMarker(markerLatitude, markerLongitude, markerDescription);;
  }
}

function setGeonameIdInForm(geonameId, latitude, longitude, geonamename, countryname, countrycode, admincode) {
    $('geonameId').value = geonameId;
    $('latitude').value = latitude;
    $('longitude').value = longitude;
    $('geonamename').value = geonamename;
    $('countryname').value = countryname;
    $('geonamecountrycode').value = countrycode;
    $('admincode').value = admincode;
    $('countryname').value = countryname;
    $('newgeo').value = 1;
}
