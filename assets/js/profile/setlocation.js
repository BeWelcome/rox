import {initializeSingleAutoComplete} from '../suggest/locations';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';
import {addPositronLayer} from '../map/positron';

const labelText = document.getElementById('marker_label_text').value;
const locationGeonameId = document.getElementById('set_location_geoname_id');
const locationLatitude = document.getElementById('set_location_latitude');
const locationLongitude = document.getElementById('set_location_longitude');
const originalLatitude = document.getElementById('original_latitude');
const originalLongitude = document.getElementById('original_longitude');

const myIcon = L.icon({
    iconUrl: 'images/icons/marker_drop.png',
    iconSize: [29, 24],
    iconAnchor: [9, 21],
    popupAnchor: [0, -14]
});
let marker = L.marker([locationLatitude.value, locationLongitude.value], {
    icon: myIcon,
    draggable: true
});
marker.bindPopup(labelText);
marker.on('dragend', dragend);
const map = L.map('map');
// No location yet: the whole world, without a marker, until a place is picked
const hasLocation = locationLatitude.value !== '' && locationLongitude.value !== ''
    && !(parseFloat(locationLatitude.value) === 0 && parseFloat(locationLongitude.value) === 0);
if (hasLocation) {
    marker.addTo(map);
    map.setView([locationLatitude.value, locationLongitude.value], 12);
} else {
    map.setView([30, 10], 2);
}

// callback when a selection is done from the list of possible results
const addMarkerAndMoveToNewLocation = function(element, result) {
    locationGeonameId.value = result.id;
    locationLatitude.value = result.latitude;
    locationLongitude.value = result.longitude;
    originalLatitude.value = result.latitude;
    originalLongitude.value = result.longitude;

    map.removeLayer(marker);

    marker = L.marker([locationLatitude.value, locationLongitude.value], {
        icon: myIcon,
        draggable: true
    });
    marker.bindPopup(labelText);
    marker.on('dragend', dragend);
    marker.addTo(map);
    map.setView(new L.LatLng(locationLatitude.value, locationLongitude.value), 12, {animate: true});
};

// Dragging the marker around on the map changes the stored lat, long
function dragend(e) {
    const originalLatLng = L.latLng(originalLatitude.value, originalLongitude.value);
    locationLatitude.value = e.target._latlng.lat;
    locationLongitude.value = e.target._latlng.lng;
}

initializeSingleAutoComplete("/suggest/locations/places/exact", 'js-location-picker', addMarkerAndMoveToNewLocation);

addPositronLayer(map);

L.control.scale().addTo(map);

