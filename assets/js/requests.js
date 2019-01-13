require('./tempusdominus.js');

$(function () {
    let arrival = $('#arrival');
    let departure = $('#departure');
    let lang = document.documentElement.lang;
    arrival.datetimepicker({
        locale: lang,
        keepInvalid: true,
        format: 'yyyy-MM-dd',
        minDate: Date.now(),
    });
    departure.datetimepicker({
        locale: lang,
        keepInvalid: true,
        useCurrent: false,
        format: 'yyyy-MM-dd'
    });
    arrival.on("change.datetimepicker", function (e) {
        $('#departure').datetimepicker('minDate', e.date.add(1, 'days'));
    });
    departure.on("change.datetimepicker", function (e) {
        $('#arrival').datetimepicker('maxDate', e.date.subtract(1, 'days'));
    });
});
