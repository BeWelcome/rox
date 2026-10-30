import {initializeSingleAutoComplete} from '../suggest/locations';

// The location field of the group search and the group settings: a place, region or country from the suggestions
// sets the geonames id in the hidden field named by data-location-target.
const locationName = document.querySelector('.js-location-picker[data-location-target]');
const location = locationName ? document.querySelector(locationName.dataset.locationTarget) : null;

if (locationName && location) {
    // On the groups pages a picked place, or the field emptied, searches at once (no Search button for it)
    const searchForm = locationName.closest('.js-group-search');
    const hadLocation = location.value !== '';

    initializeSingleAutoComplete('/suggest/locations/all', 'js-location-picker', (element, result) => {
        element.value = result.name.replaceAll('#', ', ');
        location.value = result.id;
        if (searchForm) {
            searchForm.requestSubmit();
        }
    }, searchForm ? {
        // The cross in the field, shown as soon as a place is there: it empties the field and searches without it
        clearButtonOnInitial: true,
        onReset: () => {
            location.value = '';
            if (hadLocation) {
                searchForm.requestSubmit();
            }
        },
    } : {});

    // Typing something else drops the picked location.
    locationName.addEventListener('input', () => {
        location.value = '';
        if (searchForm && hadLocation && locationName.value.trim() === '') {
            searchForm.requestSubmit();
        }
    });
}
