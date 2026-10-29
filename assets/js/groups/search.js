import {initializeSingleAutoComplete} from '../suggest/locations';

// The location filter of the group search: a place, region or country from the suggestions sets the hidden geonames id.
const locationName = document.getElementById('group_search_location_name');
const location = document.getElementById('group_search_location');

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
