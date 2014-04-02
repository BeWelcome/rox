jQuery(function() {
  // document loaded
  initOsmMap();
});

var mapBuilder;

function initOsmMap() {

  var markerLatitude = jQuery('#markerLatitude').val();
  var markerLongitude = jQuery('#markerLongitude').val();
  if (jQuery('#geonamesmap').length > 0 && markerLatitude != null && markerLongitude != null){

    mapBuilder = new BWSimpleMapBuilder("geonamesmap", false);
    // zoom map to specified location
    var zoomLevel = 8;
    mapBuilder.setCenter(markerLatitude, markerLongitude, zoomLevel);

    var markerDescription = jQuery('#markerDescription').val();
    if (markerDescription != null) {
      // add marker
      mapBuilder.addSimpleMarker(markerLatitude, markerLongitude, markerDescription);;
    }
  }
}