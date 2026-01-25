import { Calendar } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.css';
import * as dayjs from 'dayjs'

export { initializeCalendar }

const htmlTag = document.getElementsByTagName('html')[0];
const lang = htmlTag.attributes['lang'].value;

const now = dayjs()
const minimumAge = now.subtract(18, 'year');
const maximumAge = now.subtract(120, 'year');

const options = {
    inputMode: true,
    type: 'default',
    onChangeToInput(self) {
        if (!self.context.inputElement) return;
        if (self.context.selectedDates[0]) {
            self.context.inputElement.value = self.context.selectedDates[0];
            self.hide();
        } else {
            self.context.inputElement.value = '';
        }
    },
    lang: lang,
    dateMin: maximumAge.format('YYYY-MM-DD'),
    dateMax: minimumAge.format('YYYY-MM-DD'),
    positionToInput: 'auto',
    selectedTheme: 'light',
    disabledDates: [],
    dateToday: minimumAge.toDate(),
};

const initializeCalendar = (id) => {
    const calendarAnchor = document.getElementById(id);
    const calendar = new Calendar(calendarAnchor, options);
    calendar.init();
}