import VanillaCalendar, { Options, FormatDateString } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/build/vanilla-calendar.min.css';
import * as dayjs from 'dayjs'

import {initializeSingleAutoComplete} from './suggest/locations';
import * as L from 'leaflet';

import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';

const htmlTag = document.getElementsByTagName('html')[0];
const lang = htmlTag.attributes['lang'].value;

const minimumAge = dayjs().subtract(18, 'year');
const maximumAge = minimumAge.subtract(122, 'year');

const options: Options = {
    input: true,
    type: 'default',
    date: {
        max: <FormatDateString>minimumAge.format('YYYY-MM-DD'),
        min: <FormatDateString>maximumAge.format('YYYY-MM-DD')
    },
    actions: {
        changeToInput(e, calendar, self) {
            if (!self.HTMLInputElement) return;
            if (self.selectedDates[0]) {
                self.HTMLInputElement.value = self.selectedDates[0];
                calendar.hide();
            } else {
                self.HTMLInputElement.value = '';
            }
        },
    },
    settings: {
        lang: lang,
        visibility: {
            positionToInput: 'center',
            theme: 'light',
            disabled: false,
        },
    },
};

const birthDate = document.getElementById('signup_form_finalize_birthdate');
const calendar = new VanillaCalendar(birthDate, options);
calendar.init();

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
marker.addTo(map);
map.setView([locationLatitude.value, locationLongitude.value], 12);

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

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>',
    subdomains: ['a', 'b', 'c']
}).addTo(map);

L.control.scale().addTo(map);

