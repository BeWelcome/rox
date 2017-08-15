$(function () {

    $('#activity-start-date').datetimepicker({
        debug: true
    });
    $('#activity-end-date').datetimepicker({
        useCurrent: false
    });
    $('#activity-start-date').on("change.datetimepicker", function (e) {
        $('#activity-end-date').datetimepicker('minDate', e.date);
    });
    $('#activity-end-date').on("change.datetimepicker", function (e) {
        $('#activity-start-date').datetimepicker('maxDate', e.date);
    });
});
