jQuery(function() {
	// document loaded
	initOsmMap();
});

var mapBuilder;

function initOsmMap() {
	
	var markerLatitude = jQuery('#markerLatitude').val();
	var markerLongitude = jQuery('#markerLongitude').val();
	if (jQuery('#geonamesmap').length > 0 && markerLatitude != null && markerLongitude != null){

		var cloudmadeApiKey = jQuery('#cloudmadeApiKeyInput').val();
		
		if (cloudmadeApiKey == null || cloudmadeApiKey == ''){
			bwrox.error('CloudMade API key not defined!');
		}
		
		mapBuilder = new BWSimpleMapBuilder(cloudmadeApiKey, "geonamesmap", false);
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