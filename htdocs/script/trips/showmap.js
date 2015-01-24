function addMarkers(map) {
    var latLngs = [];
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
            latLngs[latLngs.length] = [latitude, longitude];
        }

        i++;
    });

    map.addLayer(markers);

    bwrox.debug('%s markers added to trips map.', i);

    return latLngs;
}

var map = initMap('trips-map');
var latLngs = addMarkers(map);
if (latLngs.length == 0) {
    map.fitWorld();
} else {
    map.fitBounds(latLngs);
}
