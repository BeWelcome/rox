import { Calendar } from 'vanilla-calendar-pro';
import 'vanilla-calendar-pro/styles/index.css';
import * as dayjs from 'dayjs'

import 'leaflet';
import 'leaflet/dist/leaflet.css';
import {initializeSingleAutoComplete} from '../suggest/locations';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';
import { default as rangeSlider } from 'rangeslider-pure';

const htmlTag = document.getElementsByTagName('html')[0];
const lang = htmlTag.attributes['lang'].value;

const minimumAge = dayjs().subtract(18, 'year');
const maximumAge = minimumAge.subtract(122, 'year');

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

const birthDate = document.getElementById('signup_form_finalize_birthdate');
const calendar = new Calendar(birthDate, options);
calendar.init();

const labelText = 'marker-label'; // document.getElementById('marker_label_text').value;
const locationGeonameId = document.getElementById('signup_form_finalize_location_geoname_id');
const locationLatitude = document.getElementById('signup_form_finalize_location_latitude');
const locationLongitude = document.getElementById('signup_form_finalize_location_longitude');

const myIcon = L.icon({
    iconUrl: 'images/icons/marker_drop.png',
    iconSize: [29, 24],
    iconAnchor: [9, 21],
    popupAnchor: [0, -14]
});

// callback when a selection is done from the list of possible results
const storeLocation = function(element, result) {
    locationGeonameId.value = result.id;
    locationLatitude.value = result.latitude;
    locationLongitude.value = result.longitude;
};

initializeSingleAutoComplete("/suggest/locations/places/exact", 'js-location-picker', storeLocation);

const slider = document.querySelectorAll('input[type="range"]');

function updateValueOutput(value) {
    const valueOutput = document.getElementsByClassName('rangeSlider__value-output');
    if (valueOutput.length) {
        valueOutput[0].innerHTML = markers[value];
    }
}

const initializeSlider = () => {
    return rangeSlider.create(slider, {
        onInit: function() {
            updateValueOutput(0);
        },
        onSlide: function(value, percent, position) {
            updateValueOutput(value);
        }
    });
};

initializeSlider();

const accommodationRadiobuttons = document.querySelectorAll(".btn-light");
const hostingInterest = document.getElementById('hosting_interest');
const radioHandler = (event) => {
    if (event.target.type === 'radio') {
        console.log("Clicked: ", event.target.type, event.target.checked, event.target.value);
        if (event.target.value === 'no') {
            hostingInterest.classList.remove('u:block');
            hostingInterest.classList.add('u:hidden');
        } else {
            hostingInterest.classList.remove('u:hidden');
            hostingInterest.classList.add('u:block');
        }
    }
}

for (let radio of accommodationRadiobuttons) {
    radio.addEventListener("click", radioHandler)
}
