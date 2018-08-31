$(function () {
if ($('#map').length) {
    var map = L.map('map', {
        center: [15, 0],
        minZoom: 2,
        zoom: 2
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="/copyright">OpenStreetMap contributors</a>',
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
                iconSize: [40, 40],
                className: '',
                html: '<div class="cluster_count" style="text-align:center;' +
                'color:#fff;' +
                'background-color:#69bb11;">' + cluster.getChildCount() + '</div>'
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
        var iconFile;
        switch(value.Accommodation) {
            case 'anytime':
                iconFile = 'anytime';
                break;
            case 'dependonrequest':
                iconFile = 'dependonrequest';
                break;
            case 'dontask':
                iconFile = 'neverask';
                break;
        }
        var icon = new L.DivIcon({ html: '<div><img src="/images/icons/' + iconFile + '.png" class="mapicon"></div>', className: '', iconSize: new L.Point(17, 17) });
        var marker = new L.marker([value.latitude, value.longitude], {icon: icon, className: 'marker-cluster marker-cluster-unique'});
        value.MaxGuest = 9; //TODO: get the real maxGuest number
        var popupContent = '<div class="d-flex"><div><img src="/members/avatar/' + value.Username + '?size=50"></div>' +
            '<div class="hosticon nowrap"><img src="/images/icons/' + iconFile + '.png"><i class="fa fa-2x fa-bed p-1"></i><span class="h4">' + value.MaxGuest + '</span></div></div>' +
            '<div class="d-flex"><h5 class="nowrap"><a href="/members/' + value.Username + '" target="_blank">' + value.Username + '</a></h5></div>';

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
