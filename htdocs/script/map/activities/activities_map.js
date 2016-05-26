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
	
	var mapHtmlId = 'activities-map';
	
	var osmTilesProviderBaseUrl = jQuery('#osm-tiles-provider-base-url').val();
	var osmTilesProviderApiKey = jQuery('#osm-tiles-provider-api-key').val();
	
	if (osmTilesProviderBaseUrl != null){
	
		console.debug('Initialize activities map with OSM tiles provider \'%s\' and API key \'%s\' on map id \'%s\'.', osmTilesProviderBaseUrl, osmTilesProviderApiKey, mapHtmlId);
		
		var map = L.map(mapHtmlId);
		
		// configure the OSM tiles provider
        // no API KEY is currently required
        var osmLayerUrl = osmTilesProviderBaseUrl;
		
		// OSM map attribution
		var mapAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a>';
	
		L.tileLayer(osmLayerUrl, {
		    attribution: mapAttribution,
		    maxZoom: 14
		}).addTo(map);
		
		return map;
		
	}else{
		console.debug('Unable to initialize OSM layer: please set "osm_tiles_provider_base_url" property in [map] section of rox_local.ini file.');
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
		var address =  jQuery(cols[6]).html();
		
		var marker = new L.Marker([latitude, longitude], {icon: icon});
		
		var popupContent = '<h4><a href="' + activityUrl + '">' + activityTitle + '</a></h4>';
		popupContent += '<p class="date-start">' + dateStart + '</p>';
		popupContent += '<p>' + locationName + '</p>';
		popupContent += '<p class="address">' + address + '</p>';
			
		marker.bindPopup(popupContent).openPopup();
		
		markers.addLayer(marker);
		
		i++;
	});
	
	map.addLayer(markers);
	
	
	console.debug('%s markers added to activities map.', i);
	
	return markers;
}
