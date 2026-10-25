import {initializeSingleAutoComplete} from '../suggest/locations';

// The place of the activity (App\Form\SearchLocationType): the suggestion fills the hidden fields next to it.
function onChange(element, result) {
    const baseId = element.id.replace('_location_fullname', '_location_');
    element.value = result.name.replaceAll('#', ', ');
    document.getElementById(baseId + 'name').value = result.name.split('#')[0];
    document.getElementById(baseId + 'geoname_id').value = result.id;
    document.getElementById(baseId + 'latitude').value = result.latitude;
    document.getElementById(baseId + 'longitude').value = result.longitude;
}

initializeSingleAutoComplete('/suggest/locations/places', 'js-location-picker', onChange);
