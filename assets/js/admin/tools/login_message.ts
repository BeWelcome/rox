import VanillaCalendar, { Options } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/build/vanilla-calendar.min.css';

const options: Options = {
    input: true,
    type: 'default',
    actions: {
        changeToInput(e, calendar, self) {
            if (!self.HTMLInputElement) return;
            if (self.selectedDates[0]) {
                self.HTMLInputElement.value = self.selectedDates[0] + " " + self.selectedTime;
            } else {
                self.HTMLInputElement.value = '';
            }
        },
    },
    settings: {
        range: {
            disablePast: true,
        },
        selection: {
            time: 24,
        },
        visibility: {
            positionToInput: 'center',
            theme: 'light',
        },
    },
};

const expires = document.getElementById('login_message_expires');
const calendar = new VanillaCalendar(expires, options);
calendar.init();
