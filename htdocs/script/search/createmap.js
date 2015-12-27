var map = L.map( 'map', {
    center: [20.0, 105.0],
    minZoom: 2,
    zoom: 2
});

L.tileLayer( 'https://otile3-s.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://osm.org/copyright" title="OpenStreetMap" target="_blank">OpenStreetMap</a> contributors | Tiles Courtesy of <a href="http://www.mapquest.com/" title="MapQuest" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png" width="16" height="16">',
    subdomains: ['otile1','otile2','otile3','otile4']
}).addTo( map );

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

    var i = 0;

    var markers = new L.markerClusterGroup();

    $('#mapresults tr').each(function(index, value) {

        // for each row of data
        var cols = $(this).children('td');

        // cols: activity title, location name, location latitude, location longitude, activity details link URL
        var accommodation = $(cols[0]).html();
        var username = $(cols[1]).html();
        var latitude = $(cols[2]).html();
        var longitude = $(cols[3]).html();

        var icon = new L.DivIcon({ html: '<div><img src="/images/icons/' + accommodation + '.png" width="17" height="17"></div>', className: '"leaflet-marker-icon marker-cluster marker-cluster-unique', iconSize: new L.Point(17, 17) });

        var marker = new L.Marker([latitude, longitude], {icon: icon});

        var popupContent = '<h4><img src="/members/avatar/' + username + '?xs"> <a href="/members/' + username + '">' + username+ '</a></h4>';
        popupContent += '<p>' + accommodation + '</p>';

        marker.bindPopup(popupContent).openPopup();

        // groups[accommodation].addLayer(marker);
        markers.addLayer(marker);
        i++;
    });

    try {
        map.addLayer(markers);
    }
    catch(err) {}

    var bounds =  new L.LatLngBounds(markers);
    return bounds;
}

var bounds = addMarkers(map);

map.fitBounds(bounds);