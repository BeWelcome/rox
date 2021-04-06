import SearchPicker from "./search/searchpicker";
import Litepicker from 'litepicker';
import moment from 'moment';
import '../scss/_daterangepicker.scss';

var L = require('leaflet');

require('leaflet-polylinedecorator');

let searchPicker = new SearchPicker( "/search/locations/places", 'js-search-picker');

let pickers = document.querySelectorAll('*[id*="_duration"]');
let latestDeparture = moment().add(1, 'day');

pickers.forEach(initializePicker);

function initializePicker(value) {
    const parent = value.id.replace('_duration', '');
    const picker = new Litepicker({
        element: value,
        singleMode: false,
        minDate: moment().format('YYYY-MM-DD'),
        numberOfMonths: 2,
        numberOfColumns: 2,
        format: "YYYY-MM-DD",
        lang: document.documentElement.lang,
        setup: (picker) => {
            picker.on('selected', (start, end) => {
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

$(document).on('click', '.js-btn-add[data-target]', function (event) {
    let collectionHolder = $('#' + $(this).attr('data-target'));

    if (!collectionHolder.attr('data-counter')) {
        collectionHolder.attr('data-counter', collectionHolder.children().length);
    }

    let prototype = collectionHolder.attr('data-prototype');
    let form = prototype.replace(/__name__/g, collectionHolder.attr('data-counter'));

    let counter = Number(collectionHolder.attr('data-counter'));

    collectionHolder.attr('data-counter', counter + 1);
    collectionHolder.append(form);

    /* enable a search picker on all location fields (including the newly added one */
    searchPicker = new SearchPicker( "/search/locations/places", 'js-search-picker');

    const duration = document.getElementById('trip_subtrips_' + counter + '_duration');

    initializePicker(duration);

    event && event.preventDefault();
});

$(document).on('click', '.js-btn-remove[data-related]', function (event) {
    let name = $(this).attr('data-related');
    $('*[data-content="' + name + '"]').remove();

    event && event.preventDefault();
});

$( function() {
if ($('#map').length) {

    var map = L.map('map', {
        center: [0, 0],
        zoom: 0,
        zoomSnap: 0.1
    });

    let allData = $('.js-data').map((_, el) => [el.value.split(',')]).get()

    let circlesArray = []

    let locationsArray = []

    for (let i = 0; i < allData.length; i++) {

        let location = allData[i][0]
        let latitude = allData[i][1]
        let longitude = allData[i][2]
        locationsArray.push([latitude, longitude]);

        let countryName = allData[i][3]
        let tripDate = allData[i][4]

        let marker = L.marker([latitude, longitude]).addTo(map);
        marker.bindPopup("<strong>" + location + "</strong> (" + countryName + ")<br>" + tripDate).openPopup();

        let circle = null;
        circle = L.circle([latitude, longitude], {
            color: 'rgb(112,0,243)',
            fillColor: 'rgba(112, 0, 243, 0.1)',
            fillOpacity: 1,
            radius: 25000
        }).addTo(map);
        circlesArray.push(circle)
    }

    let group = new L.featureGroup(circlesArray);

    // \todo In case of trip of someone else check if own circle intersects with travel
    // if so show on map as well.
    map.fitBounds(group.getBounds());

    let journey = L.polyline(locationsArray).addTo(map);
    var arrows = L.polylineDecorator(journey, {
        patterns: [
            {offset: 25, repeat: 50, symbol: L.Symbol.arrowHead({pixelSize: 15, pathOptions: {fillOpacity: 1, weight: 0}})}
        ]
    }).addTo(map);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="/about/credits#OSM">OpenStreetMap contributors</a>',
        subdomains: ['a', 'b', 'c']
    }).addTo(map);
}});
