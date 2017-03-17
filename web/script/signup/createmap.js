var map = L.map( 'map', {
    center: [20.0, 5.0],
    minZoom: 2,
    zoom: 2
});

L.tileLayer( 'http://{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://osm.org/copyright" title="OpenStreetMap" target="_blank">OpenStreetMap</a> contributors | Tiles Courtesy of <a href="http://www.mapquest.com/" title="MapQuest" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png" width="16" height="16">',
    subdomains: ['otile1','otile2','otile3','otile4']
}).addTo( map );

//
var myIcon = L.icon({
    iconUrl: 'images/icons/marker_drop.png',
    iconSize: [29, 24],
    iconAnchor: [9, 21],
    popupAnchor: [0, -14]
});

// addMarker function used by the auto completion
var addMarker = function(id, label, geonameid, latitude, longitude) {
    if (marker != null) {
        map.removeLayer(marker);
    }
    marker = L.marker([latitude, longitude],
        {
            icon: myIcon,
            draggable: true
        });
    marker.bindPopup(label);
    marker.on('dragend', dragend);
    marker.addTo(map);
    map.setView(new L.LatLng(latitude, longitude), 12, {animate: true});
};

function dragend(e) {
    $('#location-latitude').val(e.target._latlng.lat);
    $('#location-longitude').val(e.target._latlng.lng);
}

var marker = null;
var locationName  = $("#location").val();
var geonameId = $("#location-geoname-id").val();
var latitude = $("#location-latitude").val();
var longitude = $("#location-longitude").val();
if (latitude && longitude) {
    addMarker(0, locationName, geonameId, latitude, longitude);
}
