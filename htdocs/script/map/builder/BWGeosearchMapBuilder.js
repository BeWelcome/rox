/**
 * Map builder.
 *
 */
var BWGeosearchMapBuilder = Class
    .create({
      /**
       * Constructor: initialize the map
       */
      initialize : function(mapHtmlId, mapoff) {

    	var osmTilesProviderBaseUrl = jQuery('#osm-tiles-provider-base-url').val();
    	var osmTilesProviderApiKey = jQuery('#osm-tiles-provider-api-key').val();
    		
    	this.mapoff = mapoff;
        
    	if (!this.mapoff){
    	
	    	if (osmTilesProviderBaseUrl != null){
	    		
	    		bwrox.debug('Initialize activities map with OSM tiles provider \'%s\' and API key \'%s\' on map id \'%s\'.', osmTilesProviderBaseUrl, osmTilesProviderApiKey, mapHtmlId);
	    			
		        this.isInitialized = false;
		        this.mapHtmlId = mapHtmlId;
		        // set zoom limits
		        this.initialZoomLevel = 1;
		        this.minZoom = 1;
		        this.maxZoom = 18;
	
				// configure the OSM tiles provider
		        // no API KEY is currently required
		        this.osmLayerUrl = osmTilesProviderBaseUrl;
		
		        // map attribution
				var mapAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a>';
		
		        this.labelAccomodation1 = jQuery('#accomodation1').html();
		        this.labelAccomodation2 = jQuery('#accomodation2').html();
		        this.labelAccomodation3 = jQuery('#accomodation3').html();
		
		        this.currentlyOpenMarkerIndex = null;
		        this.maxZIndexOffset = 100;
		
		        this.memberListOffsets = null;
		
		        this.isLayerGroup1Used = false;
		        this.isLayerGroup2Used = false;
		        this.isLayerGroup3Used = false;
		
		        this.markers = new Array();
		
		        // init map
		        this.initMap();
		        
	    	}else{
	    		bwrox.debug('Unable to initialize OSM layer: please set "osm_tiles_provider_base_url" property in [map] section of rox_local.ini file.');
	    		return null;
	    	}
    	}
      },
      /**
       * Clear the map
       */
      clearMap : function() {
        if (!this.mapoff) {
          bwrox.debug('Removing %s markers.', this.markers.length);
          for (var i = 0; i < this.markers.length; i++){
            // bwrox.debug('Remove marker %s ...', i);
            this.layerGroup1.removeLayer(this.markers[i]);
            this.layerGroup2.removeLayer(this.markers[i]);
            this.layerGroup3.removeLayer(this.markers[i]);
          }
          this.markers = new Array();

          this.memberListOffsets = null;

          if (!this.isLayerGroup1Used){
            bwrox.debug('Show layer group 1 (yes)');
            this.layersControl.addOverlay(this.layerGroup1, this.labelAccomodation1);
          }
          if (!this.isLayerGroup2Used){
            bwrox.debug('Show layer group 2 (maybe)');
            this.layersControl.addOverlay(this.layerGroup2, this.labelAccomodation2);
          }
          if (!this.isLayerGroup3Used){
            bwrox.debug('Show layer group 3 (no)');
            this.layersControl.addOverlay(this.layerGroup3, this.labelAccomodation3);
          }

          this.isLayerGroup1Used = false;
          this.isLayerGroup2Used = false;
          this.isLayerGroup3Used = false;

          bwrox.debug('Map clear');
        }
      },
      /**
       * Map first initialization.
       */
      initMap : function() {
        if (this.mapoff) {
          bwrox.info('Map is disabled');
          return;
        }
        if (this.isInitialized) {
          bwrox.warn('Map is already initialized!');
          return;
        } else {
          bwrox.debug('Map "%s" initializing... from url %s', this.mapHtmlId, this.osmLayerUrl);
        }

        // create the map
        this.osmMap = new L.Map(this.mapHtmlId, {attributionControl: true});

        // OSM layer
        this.osmLayer = new L.TileLayer(this.osmLayerUrl, {
          maxZoom : this.maxZoom,
          attribution : this.mapAttribution
        });

        this.osmMap.addLayer(this.osmLayer);

        // Google map layer
        var googleLayer = new L.Google('ROADMAP');

        this.baseMaps = {
          'OpenStreetMap' : this.osmLayer
         ,'Google Maps': googleLayer
        };

        this.layerGroups = {
            'OpenStreetMap' : this.osmLayer
           ,'GoogleMap': googleLayer
          };

        this.initLayersGroups();

        // center the map
        bwrox.debug('Set default center and zoom.');
        this.setCenter(25, 10, this.initialZoomLevel);

        // add scale
        bwrox.debug('Add scale.');
        L.control.scale({position: 'bottomright'}).addTo(this.osmMap);

        // mark map as initialized
        this.isInitialized = true;

        bwrox.debug('Map initialized!');
      },
      /**
       * Create the layers groups and controls
       */
      initLayersGroups: function(){
        // add group layers
        bwrox.debug('Adding group layers');

        // first group : Yes, be welcome
        this.layerGroup1 = new L.LayerGroup();
        this.addLayer(this.layerGroup1);

        // second group : Maybe
        this.layerGroup2 = new L.LayerGroup();
        this.addLayer(this.layerGroup2);

        // third group : No
        this.layerGroup3 = new L.LayerGroup();
        this.addLayer(this.layerGroup3);

        bwrox.debug('Adding layers control');

        this.overlayMaps= new Array();
        this.overlayMaps[this.labelAccomodation1] = this.layerGroup1;
        this.overlayMaps[this.labelAccomodation2] = this.layerGroup2;
        this.overlayMaps[this.labelAccomodation3] = this.layerGroup3;

        // layers control
        this.layersControl = L.control.layers(this.baseMaps, this.overlayMaps);
        this.layersControl.addTo(this.osmMap);
      },
      /**
       * Remove the unused layers control (associated with the layers without any markers)
       */
      removeUnusedLayersControls: function(){
        if (!this.mapoff) {
          if (!this.isLayerGroup1Used){
            bwrox.debug('Hide layer group 1 (yes)');
            this.layersControl.removeLayer(this.layerGroup1);
          }
          if (!this.isLayerGroup2Used){
            bwrox.debug('Hide layer group 2 (maybe)');
            this.layersControl.removeLayer(this.layerGroup2);
          }
          if (!this.isLayerGroup3Used){
            bwrox.debug('Hide layer group 3 (no)');
            this.layersControl.removeLayer(this.layerGroup3);
          }
        }
      },
      /**
       * Add a layer to the map.
       */
      addLayer : function(layer){
        if (!this.mapoff) {
          this.osmMap.addLayer(layer);
        }
      },
      /**
       * Add a marker to the map
       */
      addMarker : function(marker) {
        if (!this.mapoff) {
          this.map.addOverlay(marker);
        }
      },
      /**
       * Retrieve the bounds of the map
       */
      getBounds: function(){
        if (!this.mapoff) {
          return this.osmMap.getBounds();
        }else{
          return null;
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
          bwrox.debug('Center to (%d, %d) with zoom level %d.', latitude, longitude, zoomLevel);
          this.osmMap.setView(new L.LatLng(latitude, longitude), zoomLevel);
        }
      },
      /**
       * Retrieve the current zoom level
       * @param zoomLevel
       * @returns the current zoom level
       */
      getZoom : function(zoomLevel){
        return this.osmMap.getZoom();
      },
      /**
       * Set the zoom level
       */
      setZoom : function(zoomLevel){
        if (zoomLevel > this.maxZoom){
          // control max zoom
          zoomLevel = maxZoom;
        }else if (zoomLevel < this.minZoom){
          // control min zoom
          zoomLevel = this.minZoom;
        }
        bwrox.debug('Zoom to level %d.', zoomLevel);
        this.osmMap.setZoom(zoomLevel);
      },
      /**
       * Force to refresh the map
       */
      refresh: function(){
        if (!this.mapoff) {
          bwrox.debug('Force to refresh the map');
          this.osmMap.invalidateSize();
        }
      },
      /**
       * Get the last screen member offset
       * @param markerIndex
       * @returns
       */
      getScreenMemberOffset: function(markerIndex){
        if (this.memberListOffsets == null){
          // init
          this.memberListOffsets = new Array();
          var totalOffset = 0;
          var spaceBetweenItems = 6;
          for (var i = 0; i < this.markers.length; i++){
            var memberDetailSelector = '#memberDetail'+(i+1);
            // calculate member details size
            var offset = jQuery(memberDetailSelector).height();
            bwrox.debug('Member detail %d offset is %d (total=%d)', i, offset, totalOffset);
            // store it in the array
            this.memberListOffsets[i] = totalOffset;
            totalOffset += offset + spaceBetweenItems;
          }
        }
        return this.memberListOffsets[markerIndex];
      },
      /**
       * Add a host marker at the specified point
       * @param point
       * @param label
       * @returns
       */
      addHostMarker : function(point, label) {

        var markerIndex = this.markers.length;

        var markerNonZeroIndex = markerIndex + 1;

        // create DIV icon marker
        var markerClassName;
        if(point.accomodation == 'anytime'){
          markerClassName = 'anytime-marker-icon';
        }else if(point.accomodation == 'neverask'){
          markerClassName = 'neverask-marker-icon';
        }else{
          // maybe
          markerClassName = 'maybe-marker-icon';
        }

        var icon = L.divIcon({className: markerClassName, html: label});

        marker = L.marker([point.latitude, point.longitude], {icon : icon});
        this.markers[markerIndex] = marker;

        // add the marker to the right layer
        if(point.accomodation == 'anytime'){
          this.layerGroup1.addLayer(marker);
          this.isLayerGroup1Used = true;
          // 'anytime' should by default be in the front of the map
          marker.setZIndexOffset(80);
        }else if(point.accomodation == 'neverask'){
          this.layerGroup3.addLayer(marker);
          this.isLayerGroup3Used = true;
          // 'neverask' should by default be in the back of the map
          marker.setZIndexOffset(10);
        }else{
          // maybe
          this.layerGroup2.addLayer(marker);
          this.isLayerGroup2Used = true;
          // 'maybe' should by default be in the back of the map
          marker.setZIndexOffset(50);
        }

        // configure the popup to be displayed on marker click
        marker.bindPopup(point.summary, {
          autoPanPadding: new L.Point(50, 50),
          offset: new L.Point(5, 0)
        });

        var currentInstance = this;

        jQuery(marker).on('click', function(e){
          // store the last open index
          currentInstance.currentlyOpenMarkerIndex = this.index;
        });

        return marker;
      },
      /**
       * Open the specified index marker
       */
      openMarker : function(num){
        var markerIndex = num-1;
        if (this.currentlyOpenMarkerIndex != markerIndex){
          // auto open only if marker is not already open
          var marker = this.markers[markerIndex];

          // put marker to front (in case of several markers in the same area)
          this.maxZIndexOffset += 10;
          marker.setZIndexOffset(this.maxZIndexOffset);

          // simulate a click on the marker to open the popup
          marker.fireEvent('click');
        }
      },
      /**
       * Unhighlight the specified index marker
       */
      unhighlightMarker: function(num){
        var markerIndex = num-1;
        var marker = this.markers[markerIndex];
        jQuery(marker._icon).removeClass('highlighted-leaflet-marker-icon');
      },
      /**
       * Highlight the specified index marker
       */
      highlightMarker : function(num){
        var markerIndex = num-1;
        var marker = this.markers[markerIndex];

        // put marker to front (in case of several markers in the same area)
        this.maxZIndexOffset += 10;
        marker.setZIndexOffset(this.maxZIndexOffset);

        // add a class in order to hightlight the marker
        jQuery(marker._icon).addClass('highlighted-leaflet-marker-icon');

      },
      /**
       * Zoom in to the specified position
       * @param latitude
       * @param longitude
       */
      zoomIn : function(latitude, longitude){
        var newZoomLevel = this.getZoom() + 4;
        if (newZoomLevel < 9){
          // zoom to minimal city level zoom
          newZoomLevel = 9;
        }
        // update zoom
        this.setCenter(latitude, longitude, newZoomLevel);
      },
      /**
       * Zoom out
       */
      zoomOut: function (){
        var newZoomLevel = this.getZoom() - 4;
        // update zoom
        this.setZoom(newZoomLevel);
      }
    });
