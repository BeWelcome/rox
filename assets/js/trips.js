import dayjs from 'dayjs';

import L from 'leaflet';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';
import 'leaflet/dist/leaflet.css';
import {addPositronLayer} from './map/positron';

require('leaflet-polylinedecorator');
import {initializeMultipleAutoCompletes} from './suggest/locations';
import {initContactDrawer} from './contact/drawer';
import {initStayDates} from './contact/stay_dates';
import {prettySelect} from './pretty-select';

// Visitors page: a trip, the invitation or the message open in a side panel
initContactDrawer();

function onChange(element, result) {
    const fullName = element;
    const baseId = element.id.replace("_location_fullname", "_location_");
    const name = document.getElementById(baseId + "name");
    const geonameId = document.getElementById(baseId + "geoname_id");
    const latitude = document.getElementById(baseId + "latitude");
    const longitude = document.getElementById(baseId + "longitude");
    fullName.value = result.name.replaceAll("#", ", ");
    name.value = result.name.split("#")[0];
    geonameId.value = result.id;
    latitude.value = result.latitude;
    longitude.value = result.longitude;
}

initializeMultipleAutoCompletes("/suggest/locations/places", 'js-location-picker', onChange);

// Travellers, radius...: a menu in the site's look instead of the browser's own
document.querySelectorAll('.p-trips-form select.p-trips-select').forEach(prettySelect);

let pickers = document.querySelectorAll('*[id*="_duration"]');
let lastEndDateSet = null;

pickers.forEach(initializePicker);

// The dates of a stop: the calendar of the stay requests (contact/stay_dates.js), open
// under the stop while the dates are picked, then folded into one line with "Change".
// It fills the stop's hidden arrival and departure; the duration field only shows them.
function initializePicker(value) {
    const parent = value.id.replace('_duration', '');
    const arrival = document.getElementById(parent + '_arrival');
    const departure = document.getElementById(parent + '_departure');
    const show = () => {
        value.value = arrival.value && departure.value ? arrival.value + ' - ' + departure.value : '';
    };
    show();

    // A past stop can't be changed: its dates stay in the (disabled) field
    const stop = value.closest('.p-trips-stop');
    const texts = document.querySelector('.js-trip-dates-texts');
    if (value.disabled || !stop || !texts) {
        return;
    }

    const root = document.createElement('div');
    root.className = 'c-stay-dates p-trips-stop__dates';
    stop.classList.add('has-calendar');
    Object.assign(root.dataset, texts.dataset);
    stop.querySelector('.p-trips-stop__fields').insertAdjacentElement('afterend', root);

    [arrival, departure].forEach((input) => input.addEventListener('change', () => {
        show();
        if (departure.value) {
            lastEndDateSet = departure.value;
        }
    }));

    initStayDates(root, {
        arrival,
        departure,
        hide: [value.closest('.p-trips-field--dates')].filter(Boolean),
    });
}

document.addEventListener('click', function(event) {
    const addBtn = event.target.closest('.js-btn-add[data-target]');
    if (addBtn) {
        event.preventDefault();

        const targetId = addBtn.getAttribute('data-target');
        let collectionHolder = document.getElementById(targetId);

        if (!collectionHolder.hasAttribute('data-counter')) {
            collectionHolder.setAttribute('data-counter', collectionHolder.children.length);
        }

        let prototype = collectionHolder.getAttribute('data-prototype');
        let formHTML = prototype.replace(/__name__/g, collectionHolder.getAttribute('data-counter'));

        let counter = Number(collectionHolder.getAttribute('data-counter'));

        collectionHolder.setAttribute('data-counter', counter + 1);
        collectionHolder.insertAdjacentHTML('beforeend', formHTML);

        /* enable a search picker on all location fields (including the newly added one */
        initializeMultipleAutoCompletes("/suggest/locations/places", 'js-location-picker', onChange);

        const duration = document.getElementById('trip_legs_' + counter + '_duration');
        if (lastEndDateSet != null) {
            const arrival = document.getElementById('trip_legs_' + counter + '_arrival');
            arrival.value = lastEndDateSet;
            const nextDay = dayjs(lastEndDateSet).add(1, 'day');
            const departure = document.getElementById('trip_legs_' + counter + '_departure');
            departure.value = nextDay.format('YYYY-MM-DD');
        }

        initializePicker(duration);
    }
    
    const removeBtn = event.target.closest('.js-btn-remove[data-related]');
    if (removeBtn) {
        event.preventDefault();

        const relatedName = removeBtn.getAttribute('data-related');
        const elementsToRemove = document.querySelectorAll('*[data-content="' + relatedName + '"]');
        elementsToRemove.forEach(el => el.remove());
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('map');
    if (mapElement) {
        // data-controls="none" (the trip in the side panel): no zoom or fullscreen buttons
        const withControls = mapElement.dataset.controls !== 'none';
        // In the side panel the route keeps a margin: the route name lies on the bottom,
        // the close button on the top and, on phones, the panel's head over the bottom edge
        const tall = mapElement.offsetHeight > 200;
        const fitOptions = withControls ? {} : {
            paddingTopLeft: tall ? [40, 70] : [24, 24],
            paddingBottomRight: tall ? [70, 100] : [24, 56],
        };
        var map = L.map('map', {
            center: [0, 0],
            zoom: 0,
            zoomSnap: 0.1,
            zoomControl: withControls,
            fullscreenControl: withControls,
            fullscreenControlOptions: {
                position: 'topleft'
            }
        });

        const dataElements = document.querySelectorAll('.js-data');
        let allData = Array.from(dataElements).map(el => el.value.split(','));
        let circlesArray = [];
        let locationsArray = [];

        // The stops as numbered dots, as in the itinerary
        const stopIcon = (number) => L.divIcon({
            className: 'c-trip-pin',
            html: String(number),
            iconSize: [28, 28],
            iconAnchor: [14, 14],
            popupAnchor: [0, -14],
        });

        for (let i = 0; i < allData.length; i++) {
            let location = allData[i][0];
            let latitude = parseFloat(allData[i][1]);
            let longitude = parseFloat(allData[i][2]);

            locationsArray.push([latitude, longitude]);

            let countryName = allData[i][3];
            let tripDate = allData[i][4];

            let marker = L.marker([latitude, longitude], { icon: stopIcon(i + 1) }).addTo(map);
            marker.bindPopup("<strong>" + location + "</strong> (" + countryName + ")<br>" + tripDate);

            let circle = L.circle([latitude, longitude], {
                color: '#ea7a2e',
                weight: 1.5,
                fillColor: 'rgba(234, 122, 46, 0.08)',
                fillOpacity: 1,
                radius: trip.radius + .1
            }).addTo(map);
            circlesArray.push(circle);
        }

        // if not own trip add circle with search radius of current member
        if (trip.own === false && typeof memberInfo !== 'undefined' && memberInfo.latitude !== null) {
            // Where the logged-in member lives (not the traveller): a green dot, under the stops
            const ownIcon = L.divIcon({
                className: 'c-trip-pin c-trip-pin--own',
                iconSize: [16, 16],
                iconAnchor: [8, 8],
                popupAnchor: [0, -8],
            });
            const marker = L.marker([memberInfo.latitude, memberInfo.longitude], { icon: ownIcon, zIndexOffset: -1000 }).addTo(map);
            marker.bindPopup("<strong>Your location</strong><br>...and search radius");

            L.circle([memberInfo.latitude, memberInfo.longitude], {
                color: '#3fa75a',
                weight: 1.5,
                fillColor: 'rgba(63, 167, 90, 0.08)',
                fillOpacity: 1,
                radius: memberInfo.searchRadius * 1000
            }).addTo(map);
        }

        let group = new L.featureGroup(circlesArray);
        if (circlesArray.length > 1) {
            map.fitBounds(group.getBounds(), fitOptions);
        } else if (allData.length > 0) {
            map.setView([parseFloat(allData[0][1]), parseFloat(allData[0][2])], 12);
        }

        let journey = L.polyline(locationsArray, { color: '#ea7a2e', weight: 3, opacity: 0.85 }).addTo(map);
        var arrows = L.polylineDecorator(journey, {
            patterns: [
                { offset: 25, repeat: 50, symbol: L.Symbol.arrowHead({ pixelSize: 12, pathOptions: { color: '#ea7a2e', fillOpacity: 1, weight: 0 } }) }
            ]
        }).addTo(map);

        addPositronLayer(map);

        // detect fullscreen toggling
        map.on('enterFullscreen', function () {
            map.fitBounds(group.getBounds());
        });
        map.on('exitFullscreen', function () {
            map.fitBounds(group.getBounds());
        });
    }
});
