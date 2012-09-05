var mapBuilder;

var firstChange = true;

function initOsmMapBlogEdit() {
	if (jQuery('#spaf_map').length > 0){
		mapBuilder = new BWSimpleMapBuilder("spaf_map", false);
	
		var markerLatitude = jQuery('#markerLatitude').val();
		var markerLongitude = jQuery('#markerLongitude').val();
		if (markerLatitude != null && markerLongitude != null) {
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
}
/**
 * called when an user click on a result of the list, to update the marker.
 * */
function setMap(geonameid, latitude, longitude, zoom, geonamename, countryname, countrycode, admincode) {
    setGeonameIdInFormBlog(geonameid, latitude, longitude, geonamename, countryname, countrycode, admincode);
    changeMarkerBlog(latitude, longitude, zoom, geonamename+', '+countryname);
    Element.setStyle($('li_'+geonameid), {fontWeight:'bold',backgroundColor:'#f5f5f5',backgroundImage:'url(images/icons/tick.png)'});
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
    $('geonameid').value = geonameid;
    $('latitude').value = latitude;
    $('longitude').value = longitude;
    $('geonamename').value = geonamename;
    $('geonamecountrycode').value = countrycode;
    $('admincode').value = admincode;
}
