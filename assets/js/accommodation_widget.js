import {default as rangeSlider} from 'rangeslider-pure';
import {trans} from './translator'

export { initializeAccommodationWidget }

const accommodationRadiobuttons = document.querySelectorAll(".js-accommodation");
const hostingInterest = document.getElementById('hosting_interest');
const radioHandler = (event) => {
    if (event.target.type === 'radio') {
        if (event.target.value === 'no') {
            hostingInterest.classList.remove('u:block');
            hostingInterest.classList.add('u:hidden');
        } else {
            hostingInterest.classList.remove('u:hidden');
            hostingInterest.classList.add('u:block');
        }
        accommodationRadiobuttons.forEach( (radio) => {
            radio.parentElement.classList.remove('u:bg-gray-400')
        })
        event.target.parentElement.classList.add('u:bg-gray-400');
    }
}

const markers = [
    trans('hosting_interest.select'),
    trans('hosting_interest.very_low'),
    trans('hosting_interest.low'),
    trans('hosting_interest.lower'),
    trans('hosting_interest.low_to_medium'),
    trans('hosting_interest.medium'),
    trans('hosting_interest.medium_to_high'),
    trans('hosting_interest.high'),
    trans('hosting_interest.higher'),
    trans('hosting_interest.very_high'),
    trans('hosting_interest.cant_wait')
]

const slider = document.querySelectorAll('input[type="range"]');

function updateValueOutput(value) {
    const valueOutput = document.getElementsByClassName('rangeSlider__value-output');
    if (valueOutput.length) {
        valueOutput[0].innerHTML = markers[value];
    }
}

const initializeHostingInterestSlider = () => {
    return rangeSlider.create(slider, {
        onInit: function () {
            updateValueOutput(0);
        },
        onSlide: function (value, percent, position) {
            updateValueOutput(value);
        }
    });
};

const initializeAccommodationRadioButtons = () => {
    accommodationRadiobuttons.forEach( (radio) => {
        radio.addEventListener("click", radioHandler)
    })
}

const initializeAccommodationWidget = () => {
    initializeAccommodationRadioButtons()
    initializeHostingInterestSlider()
}
