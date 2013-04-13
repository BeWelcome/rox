jQuery.fn.exists = function(){return this.length>0;}

jQuery(function() {
	
	if (jQuery('#activitiesMap').exists()){
	
		var cloudmadeApiKey = jQuery('#cloudmadeApiKeyInput').val();
		
		if (cloudmadeApiKey != null){
		
			bwrox.debug('Initialize activities map with couldmade API key \'%s\' and style \'%s\'.', cloudmadeApiKey, bwroxConfig.cloudmade_style_id);
			
			var map = L.map('activitiesMap');
			
			// Cloudmade OSM layer
			var cloudmadeUrl = 'http://{s}.tile.cloudmade.com/' + cloudmadeApiKey + '/' + bwroxConfig.cloudmade_style_id + '/256/{z}/{x}/{y}.png';
			// OSM map attribution
			var mapAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>';

			L.tileLayer(cloudmadeUrl, {
			    attribution: mapAttribution,
			    maxZoom: 18
			}).addTo(map);
			
			var markers = new L.MarkerClusterGroup();

			
			var icon = new L.DivIcon({ html: '<div><span>1</span></div>', className: '"leaflet-marker-icon marker-cluster marker-cluster-small', iconSize: new L.Point(40, 40) });
			
			var i = 0;
			
			jQuery('#activitiesData .activityData').each(function(index, value) {
				var latitude = jQuery(this).children('.latitudeValue').val();
				var longitude = jQuery(this).children('.longitudeValue').val();
				
				var marker = new L.Marker([latitude, longitude], {icon: icon});
				
				var activityTitle = jQuery(this).children('.activityTitle').val();
				var locationName = jQuery(this).children('.locationName').val();

				var activityUrl = jQuery(this).children('.activityUrl').val();

				marker.bindPopup('<h4><a href="' + activityUrl + '">' + activityTitle + '</a></h4>' + '<p>' + locationName + '</p>').openPopup();
				
				markers.addLayer(marker);
				
				i++;
			});
			
			map.addLayer(markers);
			
			
			bwrox.debug('%s markers added to activities map.', i);
			
			var activityDataLatitudeCenter = parseFloat(jQuery('#activityDataLatitudeCenter').val());
			var activityDataLongitudeCenter = parseFloat(jQuery('#activityDataLongitudeCenter').val());
			
			var activityDataLatitudeMin = parseFloat(jQuery('#activityDataLatitudeMin').val());
			var activityDataLatitudeMax = parseFloat(jQuery('#activityDataLatitudeMax').val());
			var activityDataLongitudeMin = parseFloat(jQuery('#activityDataLongitudeMin').val());
			var activityDataLongitudeMax = parseFloat(jQuery('#activityDataLongitudeMax').val());
			
			
			var southWest = new L.LatLng(activityDataLatitudeMin, activityDataLongitudeMin);
		    var northEast = new L.LatLng(activityDataLatitudeMax, activityDataLongitudeMax);
		    var bounds = new L.LatLngBounds(southWest, northEast);
			map.fitBounds(bounds);
			
		}else{
			bwrox.debug('Cloudmade API key is missing.');
		}
		
	}
	
});