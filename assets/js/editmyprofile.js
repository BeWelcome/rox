import Litepicker from 'litepicker/dist/nocss/litepicker.umd.js';
import dayjs from 'dayjs';

const birthdate = document.getElementById('birth-date');
const maxDate = dayjs().subtract(18, "years");

if (birthdate) {
    const picker = new Litepicker({
        element: birthdate,
        singleMode: true,
        allowRepick: true,
        dropdowns: {
            "minYear":1900,
            "maxYear":2004,
            "months":true,
            "years":true},
        maxDate: maxDate,
        numberOfMonths: 1,
        numberOfColumns: 1,
        format: "YYYY-MM-DD",
        position: 'top left',
        showTooltip: false,
        lang: document.documentElement.lang,
        setup: (picker) => {
            picker.on('selected', (start, end) => {
            });
        }
    });
}
