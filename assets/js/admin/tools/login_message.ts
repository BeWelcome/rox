// @ts-ignore
import { Calendar, type Options } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.css';

const options: Options = {
    inputMode: true,
    type: 'default',
    onChangeToInput(self) {
        if (!self.context.inputElement) return;
        if (self.context.selectedDates[0]) {
            self.context.inputElement.value = self.context.selectedDates[0] + " " + self.context.selectedTime;
            self.hide();
        } else {
            self.context.inputElement.value = '';
        }
    },
    disableDatesPast: true,
    selectionTimeMode: 24,
    positionToInput: 'auto',
    selectedTheme: 'light',
};

const expires = document.getElementById('login_message_expires');
const calendar = new Calendar(expires, options);
calendar.init();
