require('../tempusdominus.js');

$(function () {
    let activityStartDate = $('#activity-start-date');
    let activityEndDate = $('#activity-end-date');
    activityStartDate.datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        sideBySide: true
    });
    activityEndDate.datetimepicker({
        format: 'YYYY-MM-DD HH:mm',
        sideBySide: true,
        useCurrent: false
    });

    activityStartDate.on("change.datetimepicker", function (e) {
        activityEndDate.datetimepicker('minDate', e.date);
    });
    activityEndDate.on("change.datetimepicker", function (e) {
        activityStartDate.datetimepicker('maxDate', e.date);
    });
});
