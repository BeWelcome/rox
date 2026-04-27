import Litepicker from 'litepicker';

import dayjs from 'dayjs';
import '../scss/_daterangepicker.scss';

import L from 'leaflet';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';
import 'leaflet/dist/leaflet.css';

require('leaflet-polylinedecorator');
import {initializeMultipleAutoCompletes} from './suggest/locations';

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

let pickers = document.querySelectorAll('*[id*="_duration"]');
let lastEndDateSet = null;

pickers.forEach(initializePicker);

function initializePicker(value) {
    const parent = value.id.replace('_duration', '');
    const picker = new Litepicker({
        element: value,
        singleMode: false,
        allowRepick: true,
        minDate: dayjs(),
        numberOfMonths: 2,
        numberOfColumns: 2,
        format: "YYYY-MM-DD",
        position: 'top left',
        lang: document.documentElement.lang,
        showTooltip: false,
        setup: (picker) => {
            picker.on('selected', (start, end) => {
                lastEndDateSet = end.format('YYYY-MM-DD');
                const leg = picker.options.element.id.replace('_duration', '');
                const arrival = document.getElementById(leg + '_arrival');
                arrival.value = start ? start.format('YYYY-MM-DD') : '';
                const departure = document.getElementById(leg + '_departure');
                departure.value = end ? end.format('YYYY-MM-DD') : '';
            });
        }
    });

    const arrival = document.getElementById(parent + '_arrival').value;
    const departure = document.getElementById(parent + '_departure').value;

    if (arrival !== "") {
        picker.setDateRange(arrival, departure);
    }
}

document.addEventListener('click', function(event) {
    if (event.target.matches('.js-btn-add[data-target]')) {
        event.preventDefault();
        
        const targetId = event.target.getAttribute('data-target');
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

        const duration = document.getElementById('trip_subtrips_' + counter + '_duration');
        if (lastEndDateSet != null) {
            const arrival = document.getElementById('trip_subtrips_' + counter + '_arrival');
            arrival.value = lastEndDateSet;
            const nextDay = dayjs(lastEndDateSet).add(1, 'day');
            const departure = document.getElementById('trip_subtrips_' + counter + '_departure');
            departure.value = nextDay.format('YYYY-MM-DD');
        }

        initializePicker(duration);
    }
    
    if (event.target.matches('.js-btn-remove[data-related]')) {
        event.preventDefault();
        
        const relatedName = event.target.getAttribute('data-related');
        const elementsToRemove = document.querySelectorAll('*[data-content="' + relatedName + '"]');
        elementsToRemove.forEach(el => el.remove());
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const mapElement = document.getElementById('map');
    if (mapElement) {
        var map = L.map('map', {
            center: [0, 0],
            zoom: 0,
            zoomSnap: 0.1,
            fullscreenControl: true,
            fullscreenControlOptions: {
                position: 'topleft'
            }
        });

        const dataElements = document.querySelectorAll('.js-data');
        let allData = Array.from(dataElements).map(el => el.value.split(','));
        let circlesArray = [];
        let locationsArray = [];

        const tripIcon = L.icon({
            iconUrl: '../images/marker.png',
            iconRetinaUrl: "../images/marker-2x.png",
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
        });

        for (let i = 0; i < allData.length; i++) {
            let location = allData[i][0];
            let latitude = parseFloat(allData[i][1]);
            let longitude = parseFloat(allData[i][2]);

            locationsArray.push([latitude, longitude]);

            let countryName = allData[i][3];
            let tripDate = allData[i][4];

            let marker = L.marker([latitude, longitude], { icon: tripIcon }).addTo(map);
            marker.bindPopup("<strong>" + location + "</strong> (" + countryName + ")<br>" + tripDate);

            let circle = L.circle([latitude, longitude], {
                color: 'rgb(112,0,243)',
                fillColor: 'rgba(112, 0, 243, 0.1)',
                fillOpacity: 1,
                radius: trip.radius + .1
            }).addTo(map);
            circlesArray.push(circle);
        }

        // if not own trip add circle with search radius of current member
        if (trip.own === false) {
            const ownIcon = L.icon({
                iconUrl: '../images/trip_marker.png',
                iconRetinaUrl: "../images/trip_marker-2x.png",
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
            });
            const marker = L.marker([memberInfo.latitude, memberInfo.longitude], { icon: ownIcon }).addTo(map);
            marker.bindPopup("<strong>Your location</strong><br>...and search radius");

            L.circle([memberInfo.latitude, memberInfo.longitude], {
                color: 'rgb(0, 184, 85)',
                fillColor: 'rgba(0, 184, 85, 0.1)',
                fillOpacity: 1,
                radius: memberInfo.searchRadius * 1000
            }).addTo(map);
        }

        let group = new L.featureGroup(circlesArray);
        if (circlesArray.length > 1) {
            map.fitBounds(group.getBounds());
        } else if (allData.length > 0) {
            map.setView([parseFloat(allData[0][1]), parseFloat(allData[0][2])], 12);
        }

        let journey = L.polyline(locationsArray).addTo(map);
        var arrows = L.polylineDecorator(journey, {
            patterns: [
                { offset: 25, repeat: 50, symbol: L.Symbol.arrowHead({ pixelSize: 15, pathOptions: { fillOpacity: 1, weight: 0 } }) }
            ]
        }).addTo(map);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="/about/credits#OSM">OpenStreetMap contributors</a>',
            subdomains: ['a', 'b', 'c']
        }).addTo(map);

        // detect fullscreen toggling
        map.on('enterFullscreen', function () {
            map.fitBounds(group.getBounds());
        });
        map.on('exitFullscreen', function () {
            map.fitBounds(group.getBounds());
        });
    }
});
