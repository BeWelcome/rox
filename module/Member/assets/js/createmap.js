$(function () {
if ($('#map').length) {
    var map = L.map('map', {
        center: [15, 0],
        minZoom: 2,
        zoom: 2
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright" title="OpenStreetMap" target="_blank">OpenStreetMap</a> contributors | Tiles Courtesy of <a href="http://www.mapquest.com/" title="MapQuest" target="_blank">MapQuest</a> <img src="https://developer.mapquest.com/content/osm/mq_logo.png" width="16" height="16">',
        subdomains: ['a', 'b', 'c']
    }).addTo(map);


    var markerClusterGroup = addMarkers(map);

    if (markerClusterGroup.getLayers().length > 0) {
        map.fitBounds(markerClusterGroup.getBounds());
    }
}
});

/* function makeGroup(accommodation, color) {
    return new L.MarkerClusterGroup({
        iconCreateFunction: function(cluster) {
            return new L.DivIcon({
                iconSize: [20, 20],
                html: '<div style="text-align:center;color:#fff;background:' +
                color + '">' + cluster.getChildCount() + '</div>'
            });
        }
    }).addTo(map);
}*/
// Check if there are results to add to the map
function addMarkers(map){
    /* var groups = {
        anytime: makeGroup('anytime', '#69bb11'),
        dependonrequest: makeGroup('dependonrequest', '#0099ea'),
        dontask: makeGroup('dontask', '#666')
    }; */

    var markers = new L.markerClusterGroup({
        iconCreateFunction: function(cluster) {
            return new L.DivIcon({
                iconSize: [40, 30],
                className: '',
                html: '<div class="cluster_count" style="border-radius: 10px;box-shadow: 10px 10px 5px #888888;text-align:center;color:#fff;background-color:#69bb11;">' + cluster.getChildCount() + '</div>'
            });
        }
    });

    /**
     * @param value.Accommodation Accommodation status enum
     * @param value.Username
     * @param value.latitude
     * @param value.longitude
     */
    $.each(mapMembers, function(index, value) {

        // TODO the icons might be easier to see on the map if they had a drop shadow.
        // Add a class to the img tag and css eg. box-shadow: 10px 10px 5px #888888;
        var icon = new L.DivIcon({ html: '<div><img src="/images/icons/' + value.Accommodation + '.gif" width="17" height="17"></div>', className: 'marker-cluster marker-cluster-unique', iconSize: new L.Point(17, 17) });
        var marker = new L.marker([value.latitude, value.longitude], {icon: icon});

        var popupContent = '<h4><img src="/members/avatar/' + value.Username + '?xs"> <a href="/members/' + value.Username + '">' + value.Username + '</a></h4>';
        popupContent += '<p>' + value.Accommodation + '</p>';

        marker.bindPopup(popupContent).openPopup();

        // groups[accommodation].addLayer(marker);
        markers.addLayer(marker);
    });

    try {
        map.addLayer(markers);
    }
    catch(err) {}

    return markers;
}
