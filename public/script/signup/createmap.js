var map = L.map( 'map', {
    center: [20.0, 5.0],
    minZoom: 2,
    zoom: 2
});

L.tileLayer( 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="http://osm.org/copyright" title="OpenStreetMap" target="_blank">OpenStreetMap</a> contributors',
    subdomains: ['a','b','c']
}).addTo( map );

//
var myIcon = L.icon({
    iconUrl: 'images/icons/marker_drop.png',
    iconSize: [29, 24],
    iconAnchor: [9, 21],
    popupAnchor: [0, -14]
});

// addMarker function used by the auto completion
var addMarker = function(id, label, geonameId, latitude, longitude) {
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
