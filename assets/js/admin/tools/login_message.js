import flatpickr from "flatpickr";

import "flatpickr/dist/themes/light.css";

const expiresDateTimePicker = flatpickr(".flatpickr", {
    dateFormat: "Y-m-d H:i",
    enableTime: true,
/*    onChange: function(selectedDates, dateStr, instance) {
        console.log(selectedDates);
        console.log(dateStr );
        console.log(instance);
        expires.value = dateStr;
    },
*/});
