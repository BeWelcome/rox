/**
 * Map builder.
 *
 */
function BWSimpleMapBuilder() {
    /**
     * Constructor
     */
    this.initialize = function (mapHtmlId, mapoff) {

        var osmTilesProviderBaseUrl = jQuery('#osm-tiles-provider-base-url').val();
        var osmTilesProviderApiKey = jQuery('#osm-tiles-provider-api-key').val();

        if (osmTilesProviderBaseUrl != null) {

            bwrox.debug('Initialize activities map with OSM tiles provider \'%s\' and API key \'%s\' on map id \'%s\'.', osmTilesProviderBaseUrl, osmTilesProviderApiKey, mapHtmlId);

            this.mapoff = mapoff;

            this.markers = [];

            // configure the OSM tiles provider
            // no API KEY is currently required
            this.osmLayerUrl = osmTilesProviderBaseUrl;

            // map attribution
            var mapAttribution = 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, Imagery courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a>';

            // create the map
            this.osmMap = new L.Map(mapHtmlId, {attributionControl: false});

            this.maxZoom = 18;

            // OSM layer
            this.osmLayer = new L.TileLayer(this.osmLayerUrl, {
                maxZoom: this.maxZoom,
                attribution: this.mapAttribution
            });

            this.osmMap.addLayer(this.osmLayer);

            // Google map layer
            var googleLayer = new L.Google('ROADMAP');

            this.baseMaps = {
                'OpenStreetMap': this.osmLayer
                , 'Google Maps': googleLayer
            };

            this.layerGroups = {
                'OpenMap': this.osmLayer
                , 'GoogleMap': googleLayer
            };

            bwrox.debug('Adding layers control');

            // layers control
            this.layersControl = L.control.layers(this.baseMaps);
            this.layersControl.addTo(this.osmMap);

            this.flagIcon = new LeafletFlagIcon();

        } else {
            bwrox.debug('Unable to initialize OSM layer: please set "osm_tiles_provider_base_url" property in [map] section of rox_local.ini file.');
            return null;
        }
    };
    /**
     * Clear the map
     */
        this.clearMap = function () {
            if (!this.mapoff) {
                bwrox.debug('Removing %s markers.', this.markers.length);
                for (var i = 0; i < this.markers.length; i++) {
                    // bwrox.debug('Remove marker %s ...', i);
                    this.osmMap.removeLayer(this.markers[i]);
                }
                this.markers = [];

                bwrox.debug('Map clear');
            }
        };
    /**
     * Force to refresh the map
     */
        this.refresh = function () {
            if (!this.mapoff) {
                bwrox.debug('Force to refresh the map');
                this.osmMap.invalidateSize();
            }
        };
    /**
     * Set map center and zoom to the specified level
     * @param latitude
     * @param longitude
     * @param zoomLevel
     */
        this.setCenter = function (latitude, longitude, zoomLevel) {
            if (!this.mapoff) {
                bwrox.debug('Center to (%d, %d) with zoom level %d.', latitude, longitude, zoomLevel);
                this.osmMap.setView(new L.LatLng(latitude, longitude), zoomLevel);
            }
        };
    /**
     * Add a simple marker to the map.
     * @param longitude
     * @param latitude
     * @param description
     * @returns {L.Marker}
     */
        this.addSimpleMarker = function (longitude, latitude, description) {
            if (!this.mapoff) {
                bwrox.debug('Add simple marker to (%d, %d) with description %s.', latitude, longitude, description);
                var marker = new L.Marker(new L.LatLng(longitude, latitude));
                this.osmMap.addLayer(marker);
                if (description != null) {
                    // configure the popup to be displayed on marker click
                    marker.bindPopup(description);
                }

                var markerIndex = this.markers.length;
                this.markers[markerIndex] = marker;

                return marker;
            }
        };
    /**
     * Add a flag marker to the map.
     * @param longitude
     * @param latitude
     * @param description
     * @returns {L.Marker}
     */
        this.addFlagMarker = function (longitude, latitude, description) {

            if (!this.mapoff) {
                var marker = new L.Marker(new L.LatLng(longitude, latitude), {icon: this.flagIcon});
                this.osmMap.addLayer(marker);
                if (description != null) {
                    // configure the popup to be displayed on marker click
                    marker.bindPopup(description);
                }

                var markerIndex = this.markers.length;
                this.markers[markerIndex] = marker;

                return marker;
            }
        };
};
