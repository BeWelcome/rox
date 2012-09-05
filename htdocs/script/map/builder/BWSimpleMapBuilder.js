/**
 * Map builder.
 * 
 */
var BWSimpleMapBuilder = Class
	.create({
		/**
		 * constructor
		 */
		initialize : function(mapHtmlId, mapoff) {
			
			this.mapoff = mapoff;
			
			this.markers = new Array();
			
			// configure the tiles provider
			var apiKey = 'f18f7e7fa8014d8ab1379c78df29f5c6';
			this.cloudmadeUrl = 'http://{s}.tile.cloudmade.com/'+apiKey+'/997/256/{z}/{x}/{y}.png';
			
			// map attribution
			this.mapAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery © <a href="http://cloudmade.com">CloudMade</a>';
			
			// create the map
			this.osmMap = new L.Map(mapHtmlId, {attributionControl: false});
			
			// OSM layer
			this.osmLayer = new L.TileLayer(this.cloudmadeUrl, {
				maxZoom : this.maxZoom,
				attribution : this.mapAttribution
			});
			
			this.osmMap.addLayer(this.osmLayer);
			
			// Google map layer
			var googleLayer = new L.Google('ROADMAP');

			this.baseMaps = {
				'Open  Map' : this.osmLayer
			 ,'Google Map': googleLayer
			};
			
			this.layerGroups = {
					'OpenMap' : this.osmLayer
				 ,'GoogleMap': googleLayer
				};

			console.debug('Adding layers control');
			
			// layers control
			this.layersControl = L.control.layers(this.baseMaps);
			this.layersControl.addTo(this.osmMap);
			
			this.flagIcon = new LeafletFlagIcon();
		},
		/**
		 * Clear the map
		 */
		clearMap : function() {
			if (!this.mapoff) {
				console.debug('Removing %s markers.', this.markers.length);
				for (var i = 0; i < this.markers.length; i++){
					// console.debug('Remove marker %s ...', i);
					this.osmMap.removeLayer(this.markers[i]);
				}
				this.markers = new Array();
				
				console.debug('Map clear');
			}
		},
		/**
		 * Force to refresh the map
		 */
		refresh: function(){
			if (!this.mapoff) {
				console.debug('Force to refresh the map');
				this.osmMap.invalidateSize();
			}
		},
		/**
		 * Set map center and zoom to the specified level
		 * @param latitude
		 * @param longitude
		 * @param zoomLevel
		 */
		setCenter : function(latitude, longitude, zoomLevel) {
			if (!this.mapoff) {
				console.debug('Center to (%d, %d) with zoom level %d.', latitude, longitude, zoomLevel);
				this.osmMap.setView(new L.LatLng(latitude, longitude), zoomLevel);
			}
		},
		/**
		 * Add a simple marker to the map.
		 * @param longitude
		 * @param latitude
		 * @param description
		 * @returns {L.Marker}
		 */
		addSimpleMarker : function(longitude, latitude, description) {
			if (!this.mapoff) {
				console.debug('Add simple marker to (%d, %d) with description %s.', latitude, longitude, description);
				var marker = new L.Marker(new L.LatLng(longitude, latitude));
				this.osmMap.addLayer(marker);
				if (description != null){
					// configure the popup to be displayed on marker click
					marker.bindPopup(description);
				}
				
				var markerIndex = this.markers.length;
				this.markers[markerIndex] = marker;
				
				return marker;
			}
		},
		/**
		 * Add a flag marker to the map.
		 * @param longitude
		 * @param latitude
		 * @param description
		 * @returns {L.Marker}
		 */
		addFlagMarker: function(longitude, latitude, description){
			
			if (!this.mapoff) {
				var marker = new L.Marker(new L.LatLng(longitude, latitude), {icon: this.flagIcon});
				this.osmMap.addLayer(marker);
				if (description != null){
					// configure the popup to be displayed on marker click
					marker.bindPopup(description);
				}
				
				var markerIndex = this.markers.length;
				this.markers[markerIndex] = marker;
				
				return marker;
			}
		}
	});