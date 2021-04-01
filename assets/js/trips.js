import SearchPicker from "./search/searchpicker";
import Litepicker from 'litepicker';
import moment from 'moment';

import '../scss/_daterangepicker.scss';

let searchPicker = new SearchPicker( "/search/locations/places", 'js-search-picker');

let pickers = document.querySelectorAll('*[id*="_duration"]');
let latestDeparture = moment().add(1, 'day');

pickers.forEach(initializePicker);

function initializePicker(value) {
    console.log("value=" , value);
    const parent = value.id.replace('_duration', '');
    console.log("parent = ", parent);
    const picker = new Litepicker({
        element: value,
        singleMode: false,
        minDate: latestDeparture.format("YYYY-MM-DD"),
        numberOfMonths: 2,
        numberOfColumns: 2,
        format: "YYYY-MM-DD",
        lang: document.documentElement.lang,
        setup: (picker) => {
            picker.on('selected', (start, end) => {
                const leg = picker.options.element.id.replace('_duration', '');
                console.log(leg);
                console.log(start, end);
                const arrival = document.getElementById(leg + '_arrival');
                arrival.value = start ? start.format('YYYY-MM-DD') : '';
                console.log(arrival.value);
                const departure = document.getElementById(leg + '_departure');
                departure.value = end ? end.format('YYYY-MM-DD') : '';
                console.log(departure.value);
            });
        }
    });

    const arrival = document.getElementById(parent + '_arrival').value;
    const departure = document.getElementById(parent + '_departure').value;

    console.log("Arrival: " + arrival);
    console.log("Departure: " + departure);

    if (arrival !== "") {
        picker.setDateRange(arrival, departure);
    }

    const departureMoment =  moment(departure);

    if (departureMoment > latestDeparture) {
        latestDeparture = departureMoment;
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
