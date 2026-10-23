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

// An orange pin, dragged to where one lives (the tip is the point)
const myIcon = L.divIcon({
    className: 'p-location-pin',
    html: '<span class="p-location-pin__shadow"></span><span class="p-location-pin__shape"></span>',
    iconSize: [48, 56],
    iconAnchor: [24, 52],
    popupAnchor: [0, -50]
});
let marker = L.marker([locationLatitude.value, locationLongitude.value], {
    icon: myIcon,
    draggable: true
});
marker.bindPopup(labelText);
marker.on('dragend', dragend);
const map = L.map('map');
// No location yet: Europe, with the pin in the middle, until a place is picked
const hasLocation = locationLatitude.value !== '' && locationLongitude.value !== ''
    && !(parseFloat(locationLatitude.value) === 0 && parseFloat(locationLongitude.value) === 0);
if (hasLocation) {
    map.setView([locationLatitude.value, locationLongitude.value], 12);
} else {
    map.setView([50, 10], 4);
    marker.setLatLng(map.getCenter());
}
marker.addTo(map);

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
    const hint = document.querySelector('.js-location-hint');
    if (hint) {
        hint.classList.add('is-done');
    }
    const originalLatLng = L.latLng(originalLatitude.value, originalLongitude.value);
    locationLatitude.value = e.target._latlng.lat;
    locationLongitude.value = e.target._latlng.lng;
}

initializeSingleAutoComplete("/suggest/locations/places/exact", 'js-location-picker', addMarkerAndMoveToNewLocation);

addPositronLayer(map);

L.control.scale().addTo(map);

