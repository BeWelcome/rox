jQuery.fn.exists = function(){return this.length>0;}

jQuery(function() {
	if (jQuery('#activities-map').exists() && jQuery('#activities-data tr').exists()){
	
		// create and init the map
		var map = initMap();
		
		if (map != null){
		
			// fit the map bounds to markers location
			fitMapToBounds(map)
			
			// add all clustered markers
			addMarkers(map);
		}
	}
});

/**
 * Create and init the map.
 * @returns the created map or null if an error occured.
 */
function initMap(){
	
	var cloudmadeApiKey = jQuery('#cloudmade-api-key-input').val();
	
	if (cloudmadeApiKey != null){
	
		bwrox.debug('Initialize activities map with couldmade API key \'%s\' and style \'%s\'.', cloudmadeApiKey, bwroxConfig.cloudmade_style_id);
		
		var map = L.map('activities-map');
		
		// Cloudmade OSM layer
		var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/' + cloudmadeApiKey + '/' + bwroxConfig.cloudmade_style_id + '/256/{z}/{x}/{y}.png';
		// OSM map attribution
		var mapAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>';
	
		L.tileLayer(cloudmadeUrl, {
		    attribution: mapAttribution,
		    maxZoom: 18
		}).addTo(map);
		
		return map;
		
	}else{
		bwrox.error('Initialize activities map with couldmade API key \'%s\' and style \'%s\'.', cloudmadeApiKey, bwroxConfig.cloudmade_style_id);
		return null;
	}
}

/**
 * Fit the map to bounds.
 * @returns the map.
 */
function fitMapToBounds(map){
	var activityDataMinLatitude =  parseFloat(jQuery('#activity-data-min-latitude').val());
	var activityDataMaxLatitude =  parseFloat(jQuery('#activity-data-max-latitude').val());
	var activityDataMinLongitude =  parseFloat(jQuery('#activity-data-min-longitude').val());
	var activityDataMaxLongitude =  parseFloat(jQuery('#activity-data-max-longitude').val());
	
	var southWest = new L.LatLng(activityDataMinLatitude, activityDataMinLongitude);
    var northEast = new L.LatLng(activityDataMaxLatitude, activityDataMaxLongitude);
    var bounds = new L.LatLngBounds(southWest, northEast);
	map.fitBounds(bounds);
}

function addMarkers(map){
	var markers = new L.MarkerClusterGroup();
	
	var icon = new L.DivIcon({ html: '<div><span>1</span></div>', className: '"leaflet-marker-icon marker-cluster marker-cluster-unique', iconSize: new L.Point(40, 40) });
	
	var i = 0;
	
	jQuery('#activities-data tr').each(function(index, value) {
		
		// for each row of data
		var cols = jQuery(this).children('td');
		
		// cols: activity title, location name, location latitude, location longitude, activity details link URL
		var activityTitle = jQuery(cols[0]).html();
		var locationName = jQuery(cols[1]).html();
		var latitude = jQuery(cols[2]).html();
		var longitude =  jQuery(cols[3]).html();
		var activityUrl =  jQuery(cols[4]).html();
		var dateStart =  jQuery(cols[5]).html();
		
		var marker = new L.Marker([latitude, longitude], {icon: icon});
		
		var popupContent = '<h4><a href="' + activityUrl + '">' + activityTitle + '</a></h4>';
		popupContent += '<p class="date-start">' + dateStart + '</p>';
		popupContent += '<p>' + locationName + '</p>';
			
		marker.bindPopup(popupContent).openPopup();
		
		markers.addLayer(marker);
		
		i++;
	});
	
	map.addLayer(markers);
	
	
	bwrox.debug('%s markers added to activities map.', i);
	
	return markers;
}
