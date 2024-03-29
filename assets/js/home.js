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

function onChange(element, result) {
    const fullName = element;
    const baseId = element.id + "_";
    const geonameId = document.getElementById(baseId + "geoname_id");
    const latitude = document.getElementById(baseId + "latitude");
    const longitude = document.getElementById(baseId + "longitude");
    fullName.value = result.name.replaceAll("#", ", ");
    geonameId.value = result.id;
    latitude.value = result.latitude;
    longitude.value = result.longitude;
}

initializeSingleAutoComplete("/suggest/locations/all", 'js-location-picker', onChange);
