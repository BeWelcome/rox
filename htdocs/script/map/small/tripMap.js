jQuery(function() {
  // document loaded
  initTripMap();
});

var mapBuilder;

function initTripMap() {
  if (jQuery('#tripMap').length > 0){

    mapBuilder = new BWSimpleMapBuilder("tripMap", false);

    // center the map
    var centerLatitude = jQuery('#centerLatitude').val();
    var centerLongitude = jQuery('#centerLongitude').val();
    var zoomLevel = jQuery('#zoomLevel').val();
    mapBuilder.setCenter(centerLatitude, centerLongitude, zoomLevel);


    // add markers
    for (var i=0 ; i<=markers.length ; i++){
      var marker = markers[i];
      if (marker != null){
        // bwrox.debug('Add marker %s to map.', marker.name);
        addMarker(marker.latitude, marker.longitude, marker.name, marker.tripId);
      }
    }

  }
}

function addMarker(markerLatitude, markerLongitude, markerDescription, tripId){
  mapBuilder.addFlagMarker(markerLatitude, markerLongitude, markerDescription);;
}