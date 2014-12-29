jQuery.fn.exists = function () {
    return this.length > 0;
}

jQuery(function () {
    if (jQuery('#trips-map').exists()) {
        map = initMap('trips-map');

        if (jQuery('#trips-data tr').exists()) {
            // create and init the map

            if (map != null) {
                // add all clustered markers
                addMarkers(map);
            }
        }
        // fit the map bounds to markers location
        fitMapToBounds(map)
    }
});

/**
 * Create and init the map.
 * @returns the created map or null if an error occured.
 */
function initMap(mapHtmlId) {

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

/**
 * Fit the map to bounds.
 * @returns the map.
 */
function fitMapToBounds(map) {
    var tripDataMinLatitude = parseFloat(jQuery('#trip-data-min-latitude').val());
    var tripDataMaxLatitude = parseFloat(jQuery('#trip-data-max-latitude').val());
    var tripDataMinLongitude = parseFloat(jQuery('#trip-data-min-longitude').val());
    var tripDataMaxLongitude = parseFloat(jQuery('#trip-data-max-longitude').val());

    tripDataMaxLatitude = (isNaN(tripDataMaxLatitude)) ? 70 : tripDataMaxLatitude;
    tripDataMinLatitude = (isNaN(tripDataMinLatitude)) ? -60 : tripDataMinLatitude;
    tripDataMaxLongitude = (isNaN(tripDataMaxLongitude)) ? 179 : tripDataMaxLongitude;
    tripDataMinLongitude = (isNaN(tripDataMinLongitude)) ? -179 : tripDataMinLongitude;

    bwrox.debug(tripDataMinLatitude, tripDataMaxLatitude, tripDataMinLongitude, tripDataMaxLongitude);
    var southWest = new L.LatLng(tripDataMinLatitude, tripDataMinLongitude);
    var northEast = new L.LatLng(tripDataMaxLatitude, tripDataMaxLongitude);
    var bounds = new L.LatLngBounds(southWest, northEast);
    map.fitBounds(bounds);
}

function addMarkers(map) {
    var markers = new L.MarkerClusterGroup();

    var icon = new L.DivIcon({
        html: '<div><span>1</span></div>',
        className: '"leaflet-marker-icon marker-cluster marker-cluster-unique',
        iconSize: new L.Point(40, 40)
    });

    var i = 0;

    jQuery('#trips-data tr').each(function (index, value) {

        // for each row of data
        var cols = jQuery(this).children('td');

        // cols: activity title, location name, location latitude, location longitude, activity details link URL
        var tripName = jQuery(cols[0]).html();
        var userName = jQuery(cols[1]).html();
        var tripStartDate = jQuery(cols[2]).html();
        var tripEndDate = jQuery(cols[3]).html();
        var latitude = jQuery(cols[4]).html();
        var longitude = jQuery(cols[5]).html();
        var tripUrl = jQuery(cols[6]).html();

        var lat = isNaN(latitude) || (latitude == "");
        var lon = isNaN(longitude) || (longitude == "");
        if (!( lat || lon )) {
            var marker = new L.Marker([
                latitude,
                longitude
            ], {icon: icon});

            var popupContent = '<h4><a href="' + tripUrl + '">' + tripName + '</a></h4>';
            popupContent += '<p>' + userName + '<br>';
            popupContent += tripStartDate + ' - ' + tripEndDate + '</p>';

            marker.bindPopup(popupContent).openPopup();

            markers.addLayer(marker);
        }

        i++;
    });

    map.addLayer(markers);

    bwrox.debug('%s markers added to trips map.', i);

    return markers;
}
