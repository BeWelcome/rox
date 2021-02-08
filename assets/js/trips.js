import SearchPicker from "./search/searchpicker";
import Lightpick from 'lightpick';
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
    const picker = new Lightpick({
        field: value,
        singleDate: false,
        minDate: latestDeparture,
        numberOfMonths: 2,
        lang: document.documentElement.lang,
        onSelect: function(start, end) {
            const arrival = document.getElementById(parent + '_arrival');
            arrival.value = start ? start.format('YYYY-MM-DD') : '';
            const departure = document.getElementById(parent + '_departure');
            departure.value = end ? end.format('YYYY-MM-DD') : '';
        }
    });

    const arrival = document.getElementById(parent + '_arrival').value;
    const departure = document.getElementById(parent + '_departure').value;

    picker.setDateRange(arrival, departure);

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

    let counter = collectionHolder.attr('data-counter');

    collectionHolder.attr('data-counter', Number(collectionHolder.attr('data-counter')) + 1);
    collectionHolder.append(form);

    /* enable a search picker on all location fields (including the newly added one */
    searchPicker = new SearchPicker( "/search/locations/places");

    const duration = 'trip_subtrips_' + counter + '_duration';
    console.log("duration = " + duration);

    const picker = new Lightpick({
        field: document.getElementById(duration),
        singleDate: false,
        minDate: latestDeparture,
        numberOfMonths: 2,
        onSelect: function(start, end){
            const leg = this._opts.field.id.replace('_duration', '');
            console.log(leg);
            const arrival = document.getElementById(leg + '_arrival');
            arrival.value = start ? start.format('YYYY-MM-DD') : '';
            const departure = document.getElementById(leg + '_departure');
            departure.value = end ? end.format('YYYY-MM-DD') : '';
        }
    });

    event && event.preventDefault();
});

$(document).on('click', '.js-btn-remove[data-related]', function (event) {
    let name = $(this).attr('data-related');
    $('*[data-content="' + name + '"]').remove();

    event && event.preventDefault();
});
