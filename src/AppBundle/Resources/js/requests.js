require('./tempusdominus.js');

$(function () {
    let arrival = $('#arrival');
    let departure = $('#departure');
    arrival.datetimepicker({
        keepInvalid: true,
        format: 'YYYY-MM-DD',
        minDate: Date.now(),
    });
    departure.datetimepicker({
        keepInvalid: true,
        useCurrent: false,
        format: 'YYYY-MM-DD'
    });
    arrival.on("change.datetimepicker", function (e) {
        $('#departure').datetimepicker('minDate', e.date.add(1, 'days'));
    });
    departure.on("change.datetimepicker", function (e) {
        $('#arrival').datetimepicker('maxDate', e.date.subtract(1, 'days'));
    });
});
