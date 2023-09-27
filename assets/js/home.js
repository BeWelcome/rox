import 'jquery';
import 'popper.js';

import 'bootstrap';
import 'bootstrap-dropdown-hover';
import '../scss/home.scss';
import 'cookieconsent/src/cookieconsent.js';
import 'cookieconsent/src/styles/animation.css';
import 'cookieconsent/src/styles/base.css';
import 'cookieconsent/src/styles/layout.css';
import 'cookieconsent/src/styles/media.css';
import 'cookieconsent/src/styles/themes/classic.css';
import 'cookieconsent/src/styles/themes/edgeless.css';
import 'select2/dist/js/select2.full.js';
import '@fortawesome/fontawesome-free/js/all.js';
import './scrollmagic.js';
import './collapsemenu.js';
import {initializeSingleAutoComplete} from './suggest/locations';

function onChange(result) {
    const locationFullName = document.getElementById('search_map_location');
    const locationGeonameId = document.getElementById('search_map_location_geoname_id');
    const locationLatitude = document.getElementById('search_map_location_latitude');
    const locationLongitude = document.getElementById('search_map_location_longitude');
    locationFullName.value = result.name.replaceAll("#", ", ");
    locationGeonameId.value = result.id;
    locationLatitude.value = result.latitude;
    locationLongitude.value = result.longitude;
}

initializeSingleAutoComplete("/suggest/locations/all", 'js-location-picker', '', onChange);
