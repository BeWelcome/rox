import {initializeSingleAutoComplete} from '../suggest/locations';

// The location field of the group search and the group settings: a place, region or country from the suggestions
// sets the geonames id in the hidden field named by data-location-target.
const locationName = document.querySelector('.js-location-picker[data-location-target]');
const location = locationName ? document.querySelector(locationName.dataset.locationTarget) : null;

if (locationName && location) {
    initializeSingleAutoComplete('/suggest/locations/all', 'js-location-picker', (element, result) => {
        element.value = result.name.replaceAll('#', ', ');
        location.value = result.id;
    });

    // Typing something else drops the picked location.
    locationName.addEventListener('input', () => {
        location.value = '';
    });
}
