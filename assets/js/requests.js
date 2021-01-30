import moment from 'moment';
import Lightpick from 'lightpick';

import '../scss/_daterangepicker.scss';

$(function () {
    const input = document.getElementsByClassName('light-picker')[0];
    console.log("input = ", input);
    const parent = input.id.replace('_duration', '');
    console.log("parent = ", parent);

    const picker = new Lightpick({
        field: input,
        singleDate: false,
        minDate: moment().add(1, 'day'),
        numberOfMonths: 2,
        lang: document.documentElement.lang,
        tooltipNights: true,
        onSelect: function(start, end) {
            const arrival = document.getElementById(parent + '_arrival');
            arrival.value = start ? start.format('YYYY-MM-DD') : '';
            const departure = document.getElementById(parent + '_departure');
            departure.value = end ? end.format('YYYY-MM-DD') : '';

            let original = document.getElementById('duration-original');
            if (null !== original) {
                original.classList.remove("d-none");
                console.log('showing');
            }
        }
    });

    const arrival = document.getElementById(parent + '_arrival').value;
    const departure = document.getElementById(parent + '_departure').value;

    picker.setDateRange(arrival, departure);
});
