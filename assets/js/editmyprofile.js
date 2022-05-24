import Litepicker from 'litepicker';
import dayjs from 'dayjs';
import '../scss/_daterangepicker.scss';

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
        lang: document.documentElement.lang,
        setup: (picker) => {
            picker.on('selected', (start, end) => {
            });
        }
    });
}
