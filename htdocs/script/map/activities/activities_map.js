jQuery(function() {
	var cloudmadeApiKey = jQuery('#cloudmadeApiKeyInput').val();
	
	var map = L.map('activitiesMap').setView([51.505, -0.09], 5);
	
	L.tileLayer('http://{s}.tile.cloudmade.com/' + cloudmadeApiKey + '/997/256/{z}/{x}/{y}.png', {
	    attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>',
	    maxZoom: 18
	}).addTo(map);
	
	var markers = new L.MarkerClusterGroup();
	
	jQuery('#activitiesData .activityData').each(function(index, value) {
		var latitude = jQuery(this).children('.latitudeValue').val();
		var longitude = jQuery(this).children('.longitudeValue').val();
		
		markers.addLayer(new L.Marker([latitude, longitude]));
		
	});
	
	map.addLayer(markers);
	
});