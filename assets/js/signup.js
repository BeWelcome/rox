import Litepicker from 'litepicker';
import dayjs from 'dayjs';
import '../scss/_daterangepicker.scss';

$(function () {
    $('[data-toggle="popover"]').popover({ html : true });

    $("#mothertongue").select2({
        theme: 'bootstrap4',
        placeholder: 'Select a language',
        allowClear: true,
        width: 'auto'
    });


    let birthdate = document.getElementById('birthdate');
    let maxDate = dayjs().subtract(18, "years");

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
});
