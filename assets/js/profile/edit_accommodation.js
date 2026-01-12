import { default as rangeSlider } from 'rangeslider-pure';

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
