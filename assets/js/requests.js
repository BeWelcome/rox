import moment from 'moment';
import Litepicker from 'litepicker';

import '../scss/_daterangepicker.scss';

$(function () {
    const input = document.getElementsByClassName('js-litepicker')[0];
    if (input !== undefined) {
        const parent = input.id.replace('_duration', '');

        const picker = new Litepicker({
            element: input,
            singleMode: false,
            minDate: moment().add(1, 'day'),
            numberOfMonths: 2,
            numberOfColumns: 2,
            lang: document.documentElement.lang,
            showTooltip: false,
            setup: (picker) => {
                picker.on('selected', (start, end) => {
                    const arrival = document.getElementById(parent + '_arrival');
                    arrival.value = start ? start.format('YYYY-MM-DD') : '';
                    const departure = document.getElementById(parent + '_departure');
                    departure.value = end ? end.format('YYYY-MM-DD') : '';

                    let original = document.getElementById('duration-original');
                    if (null !== original) {
                        original.classList.remove("d-none");
                    }
                });
            }
        });

        const arrival = document.getElementById(parent + '_arrival').value;
        const departure = document.getElementById(parent + '_departure').value;

        if (arrival !== "") {
            picker.setDateRange(arrival, departure);
        }
    }
});
