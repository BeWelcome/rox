require('./tempusdominus.js');

$(function () {
    let donationDate = $('#donate-date');
    donationDate.datetimepicker({
        locale: 'en',
        keepInvalid: true,
        format: 'DD-MM-YYYY'
    });
});
