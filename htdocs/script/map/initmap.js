/**
 * Create and init the map.
 * @returns the created map or null if an error occured.
 */
function initMap(mapHtmlId) {

    if (jQuery('#' + mapHtmlId).length == 0 ) {
            return null;
    }

    var osmTilesProviderBaseUrl = jQuery('#osm-tiles-provider-base-url').val();
    var osmTilesProviderApiKey = jQuery('#osm-tiles-provider-api-key').val();

    if (osmTilesProviderBaseUrl != null) {

        bwrox.debug('Initialize trips map with OSM tiles provider \'%s\' and API key \'%s\' on map id \'%s\'.', osmTilesProviderBaseUrl, osmTilesProviderApiKey, mapHtmlId);

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

    } else {
        bwrox.debug('Unable to initialize OSM layer: please set "osm_tiles_provider_base_url" property in [map] section of rox_local.ini file.');
        return null;
    }
}