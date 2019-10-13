global.moment = require('moment');

require('tempusdominus-bootstrap-4');

$.fn.datetimepicker.Constructor.Default = $.extend({}, $.fn.datetimepicker.Constructor.Default, {
    locale: document.getElementsByTagName('html')[0].getAttribute('lang'),
    icons: {
        time: 'fas fa-clock',
        date: 'fas fa-calendar',
        up: 'fas fa-arrow-up',
        down: 'fas fa-arrow-down',
        previous: 'fas fa-chevron-left',
        next: 'fas fa-chevron-right',
        today: 'fas fa-calendar-check-o',
        clear: 'fas fa-trash',
        close: 'fas fa-times'
    }
});

// require('tempusdominus-bootstrap-4/build/css/tempusdominus-bootstrap-4.css');
