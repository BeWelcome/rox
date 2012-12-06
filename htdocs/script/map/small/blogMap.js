jQuery(function() {
	mapList = new Array();
});

/* function called when the user click on the 'map' text link */
function displayMap(popupid,  markerLatitude, markerLongitude, markerDescription) {
	Element.setStyle(popupid, {display:'block'});
    Element.show(popupid + '_map');
	
    var blogId = popupid.replace('/' + popupid + '/g', 'map_');
    
    if (mapList[blogId] == null)
    {
        var cloudmadeApiKey = jQuery('#cloudmadeApiKeyInput').val();
    	
    	if (cloudmadeApiKey == null || cloudmadeApiKey == ''){
    		bwrox.error('CloudMade API key not defined!');
    	}
    	
    	mapBuilder = new BWSimpleMapBuilder(cloudmadeApiKey, popupid + '_map', false);
    	mapList[blogId] = mapBuilder;
    }
    else
    {
    	var mapBuilder = mapList[blogId]; 
    }

	if (markerLatitude != null && markerLongitude != null) {
		// zoom map to specified location
		var zoomLevel = 8;
		mapBuilder.setCenter(markerLatitude, markerLongitude, zoomLevel);
		
		if (markerDescription != null) {
			// add marker
			var marker = mapBuilder.addSimpleMarker(markerLatitude, markerLongitude, markerDescription);
			marker.fireEvent('click');
		}
	}
}