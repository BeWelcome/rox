import { initializeSingleAutoComplete } from '../suggest/locations';
import { initializeAccommodationWidget } from "../accommodation_widget";
import { initializeCalendar } from "../calendar";

const locationGeonameId = document.getElementById('signup_form_finalize_location_geoname_id')
const locationLatitude = document.getElementById('signup_form_finalize_location_latitude')
const locationLongitude = document.getElementById('signup_form_finalize_location_longitude')

const myIcon = L.icon({
    iconUrl: 'images/icons/marker_drop.png',
    iconSize: [29, 24],
    iconAnchor: [9, 21],
    popupAnchor: [0, -14]
})

// callback when a selection is done from the list of possible results
const storeLocation = function(element, result) {
    locationGeonameId.value = result.id
    locationLatitude.value = result.latitude
    locationLongitude.value = result.longitude
}

initializeSingleAutoComplete("/suggest/locations/places/exact", 'js-location-picker', storeLocation)
initializeAccommodationWidget()
initializeCalendar('signup_form_finalize_birthdate')

