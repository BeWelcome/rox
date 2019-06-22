require('./tempusdominus.js');

$(function () {
    let arrival = $('#arrival');
    let departure = $('#departure');
    let lang = document.documentElement.lang;
    arrival.datetimepicker({
        locale: lang,
        keepInvalid: true,
        format: 'YYYY-MM-DD',
        minDate: Date.now(),
    });
    departure.datetimepicker({
        locale: lang,
        keepInvalid: true,
        useCurrent: false,
        format: 'YYYY-MM-DD'
    });
    arrival.on("change.datetimepicker", function (e) {
        $('#departure').datetimepicker('minDate', e.date.add(1, 'days'));
        let original = $('#arrival-original');
        if (original.length) {
            original.removeClass('d-none');
        }
    });
    departure.on("change.datetimepicker", function (e) {
        $('#arrival').datetimepicker('maxDate', e.date.subtract(1, 'days'));
        let original = $('#departure-original');
        if (original.length) {
            original.removeClass('d-none');
        }
    });
});
