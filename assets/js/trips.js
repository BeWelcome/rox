import SearchPicker from "./search/searchpicker";
import "tempusdominus";

let searchPicker = new SearchPicker( "/search/locations/places");

$("#trip_subtrips_0_arrival").datetimepicker({
    format: 'YYYY-MM-DD',
    keepInvalid: true
});
$("#trip_subtrips_0_departure").datetimepicker({
    format: 'YYYY-MM-DD',
    keepInvalid: true
});

$(document).on('click', '.btn-add[data-target]', function (event) {
    let collectionHolder = $('#' + $(this).attr('data-target'));

    if (!collectionHolder.attr('data-counter')) {
        collectionHolder.attr('data-counter', collectionHolder.children().length);
    }

    let prototype = collectionHolder.attr('data-prototype');
    let form = prototype.replace(/__name__/g, collectionHolder.attr('data-counter'));

    let counter = collectionHolder.attr('data-counter');

    collectionHolder.attr('data-counter', Number(collectionHolder.attr('data-counter')) + 1);
    collectionHolder.append(form);

    searchPicker = new SearchPicker( "/search/locations/places");

    $('#trip_subtrips_' + counter + '_arrival').datetimepicker({
        format: 'YYYY-MM-DD',
        keepInvalid: true
    });
    $('#trip_subtrips_' + counter + '_departure').datetimepicker({
        format: 'YYYY-MM-DD',
        keepInvalid: true
    });

    event && event.preventDefault();

});

$(document).on('click', '.btn-remove[data-related]', function (event) {
    let name = $(this).attr('data-related');
    $('*[data-content="' + name + '"]').remove();

    event && event.preventDefault();
});
