import {initializeSingleAutoComplete} from '../suggest/locations';

function onChange(element, result) {
    const locationGeonameId = document.getElementById('activity-location_geoname_id');
    const locationLatitude = document.getElementById('activity-location_latitude');
    const locationLongitude = document.getElementById('activity-location_longitude');
    locationGeonameId.value = result.id;
    locationLatitude.value = result.latitude;
    locationLongitude.value = result.longitude;
}

initializeSingleAutoComplete("/suggest/locations/places/exact", 'js-location-picker', onChange);
