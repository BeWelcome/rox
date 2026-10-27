import {initializeSingleAutoComplete} from '../suggest/locations';
import {initializeTomSelects} from '../tom-select';
import {initMemberPreview} from './preview';
import {prettySelect} from '../pretty-select';

import L from 'leaflet';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet/dist/leaflet.css';
import {addPositronLayer} from '../map/positron';

const form = document.querySelector('.js-search-form');
const phone = window.matchMedia('(max-width: 767.98px)');

/* ---------------------------------------------------------------------------
 * Where are you going?
 * ------------------------------------------------------------------------ */

const where = document.querySelector('.js-search-where');
const locationInput = document.getElementById('search_location');
const hidden = (name) => document.getElementById('search_location_' + name);

// "Find members" only works once a place has been picked from the list.
function hasPlace() {
    return hidden('geoname_id') !== null && hidden('geoname_id').value !== '' && hidden('latitude').value !== '';
}

function updateFindButtons() {
    if (!form) {
        return;
    }
    const enabled = hasPlace();
    form.querySelectorAll('button[type=submit].p-search__find').forEach((button) => {
        button.disabled = !enabled;
    });
}

// Shows "Berlin  Land Berlin, Germany" over the field while it is not being edited.
function updateWhereDisplay() {
    updateFindButtons();
    if (!where) {
        return;
    }
    const name = hidden('name').value;
    const fullName = hidden('fullname').value;
    const radius = document.getElementById('search_distance');
    const radiusText = radius && radius.selectedIndex >= 0 ? radius.options[radius.selectedIndex].text.trim() : '';
    const rest = fullName.startsWith(name + ', ') ? fullName.slice(name.length + 2) : '';

    where.querySelector('.js-search-where-name').textContent = name;
    where.querySelector('.js-search-where-rest').textContent = rest;
    where.querySelector('.js-search-where-radius').textContent = radiusText;
    where.classList.toggle('has-place', name !== '' && locationInput.value === fullName);
}

function onChange(element, result) {
    hidden('fullname').value = result.name.replaceAll('#', ', ');
    hidden('name').value = result.name.split('#')[0];
    hidden('admin_unit').value = result.isAdminUnit;
    hidden('geoname_id').value = result.id;
    hidden('latitude').value = result.latitude;
    hidden('longitude').value = result.longitude;
    placePicked = true;
    updateWhereDisplay();

    // On phones picking a place is the search: go straight to the results.
    if (phone.matches) {
        closeWhere();
        submit();
    }
}

const locationSuggest = initializeSingleAutoComplete('/suggest/locations/all', 'js-location-picker', onChange);

function openWhere() {
    if (phone.matches) {
        where.classList.add('is-open');
        document.body.classList.add('has-search-overlay');
    }
}

function closeWhere() {
    if (!where) {
        return;
    }
    where.classList.remove('is-open');
    document.body.classList.remove('has-search-overlay');
    // Typed something but picked nothing: go back to the place of the search.
    if (hidden('fullname').value !== '' && hidden('geoname_id').value === '') {
        locationInput.value = hidden('fullname').value;
    }
    updateWhereDisplay();
}

// A click in the field to change the place: it starts empty, without the old suggestions.
// Leaving it without picking a new place puts the place of the search back.
const PLACE_FIELDS = ['fullname', 'name', 'geoname_id', 'latitude', 'longitude', 'admin_unit'];
let placePicked = false;
let placeBefore = null;

if (where && locationInput) {
    locationInput.addEventListener('focus', () => {
        where.classList.add('is-editing');
        openWhere();
        placePicked = false;
        placeBefore = null;
        if (hasPlace()) {
            placeBefore = {};
            PLACE_FIELDS.forEach((name) => { placeBefore[name] = hidden(name).value; });
            if (locationSuggest && locationSuggest.autoComplete) {
                locationSuggest.autoComplete.reset();
            } else {
                locationInput.value = '';
            }
        }
    });
    locationInput.addEventListener('blur', () => {
        // Later, so that a click on a suggestion is taken first
        setTimeout(() => {
            if (document.activeElement === locationInput) {
                return;
            }
            if (!placePicked && placeBefore && !hasPlace()) {
                PLACE_FIELDS.forEach((name) => { hidden(name).value = placeBefore[name]; });
            }
            if (!placePicked && placeBefore) {
                locationInput.value = placeBefore.fullname;
            }
            where.classList.remove('is-editing');
            updateWhereDisplay();
        }, 200);
    });
    // A new text is a new place: it has to be picked from the list again.
    locationInput.addEventListener('input', () => {
        ['geoname_id', 'latitude', 'longitude', 'admin_unit'].forEach((name) => { hidden(name).value = ''; });
        updateFindButtons();
    });
    where.querySelector('.js-search-where-back').addEventListener('click', () => {
        locationInput.blur();
        closeWhere();
    });
    // The cross only empties the field to type another place: the search stays as it is
    // (leaving the field without picking a place puts the place back, see "blur" above).
    // Caught before the suggestion box's own handler, which would forget the place.
    where.addEventListener('click', (event) => {
        if (!event.target.closest('.auto-clear')) {
            return;
        }
        event.preventDefault();
        event.stopPropagation();
        if (document.activeElement === locationInput) {
            if (locationSuggest && locationSuggest.autoComplete) {
                locationSuggest.autoComplete.reset();
            } else {
                locationInput.value = '';
            }
            locationInput.focus();
        } else {
            locationInput.focus();
        }
    }, true);
    updateWhereDisplay();
}

/* ---------------------------------------------------------------------------
 * Searching
 * ------------------------------------------------------------------------ */

function submit() {
    if (!hasPlace()) {
        return; // nothing to search yet: the filters are kept for when a place is picked
    }
    const button = form.querySelector('.js-search-find');
    if (form.requestSubmit) {
        form.requestSubmit(button);
    } else {
        button.click();
    }
}

// Quick filters tick the matching box of the panel and search at once.
const pills = document.querySelectorAll('.js-search-pill');

function syncPills() {
    pills.forEach((pill) => {
        const box = document.getElementById(pill.dataset.target);
        pill.classList.toggle('is-active', box.checked);
        pill.setAttribute('aria-pressed', box.checked ? 'true' : 'false');
    });
}

pills.forEach((pill) => {
    pill.addEventListener('click', () => {
        const box = document.getElementById(pill.dataset.target);
        box.checked = !box.checked;
        syncPills();
        submit();
    });
});

if (form) {
    form.addEventListener('change', (event) => {
        if (event.target.matches('input[type=checkbox]')) {
            syncPills();
        }
        if (event.target.matches('.js-search-autosubmit')) {
            submit();
        }
        if (event.target.id === 'search_distance') {
            updateWhereDisplay();
        }
    });

    // Sort direction: one button that flips between descending and ascending.
    const direction = form.querySelector('.js-search-direction');
    const directionToggle = form.querySelector('.js-search-direction-toggle');
    if (direction && directionToggle) {
        directionToggle.addEventListener('click', () => {
            direction.value = direction.value === direction.dataset.descending
                ? direction.dataset.ascending
                : direction.dataset.descending;
            submit();
        });
    }
}

/* ---------------------------------------------------------------------------
 * Search options panel
 * ------------------------------------------------------------------------ */

const panel = document.querySelector('.js-search-options');
const backdrop = document.querySelector('.p-search-options__backdrop');

function openOptions() {
    panel.classList.add('is-open');
    panel.setAttribute('aria-hidden', 'false');
    backdrop.hidden = false;
    document.body.classList.add('has-search-overlay');
    panel.querySelector('.js-search-options-close').focus({preventScroll: true});
}

function closeOptions() {
    panel.classList.remove('is-open');
    panel.setAttribute('aria-hidden', 'true');
    backdrop.hidden = true;
    document.body.classList.remove('has-search-overlay');
}

if (panel) {
    document.querySelectorAll('.js-search-options-open').forEach((button) => button.addEventListener('click', openOptions));
    document.querySelectorAll('.js-search-options-close').forEach((button) => button.addEventListener('click', closeOptions));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && panel.classList.contains('is-open')) {
            closeOptions();
        }
    });

    // Back to the default options, then the panel closes and the search runs again.
    panel.querySelector('.js-search-options-reset').addEventListener('click', () => {
        panel.querySelectorAll('input[type=checkbox]').forEach((box) => {
            box.checked = box.name.endsWith('[accommodation_yes]') || box.name.endsWith('[accommodation_no]');
        });
        panel.querySelectorAll('select').forEach((select) => {
            if (select.id === 'search_distance') {
                return; // the radius belongs to the place, not to the options
            }
            if (select.tomselect) {
                select.tomselect.clear();
            } else if (select.id === 'search_last_active') {
                select.value = '24';
            } else {
                select.selectedIndex = 0;
            }
        });
        panel.querySelectorAll('input[type=text]').forEach((input) => { input.value = ''; });
        syncPills();
        closeOptions();
        submit();
    });
}

// On phones the radius lives in the panel, on larger screens in the search bar.
const radius = document.querySelector('.js-search-radius');
const radiusInBar = radius ? radius.parentNode : null;
const radiusNext = radius ? radius.nextElementSibling : null;
const radiusInPanel = document.querySelector('.js-search-options-radius');

function placeRadius() {
    if (!radius || !radiusInPanel) {
        return;
    }
    if (phone.matches) {
        radiusInPanel.appendChild(radius);
    } else {
        radiusInBar.insertBefore(radius, radiusNext);
    }
}

placeRadius();

// The map sits under the sticky search bar, whose height changes with the screen size.
const searchTop = document.querySelector('.p-search__top');
if (form && searchTop && 'ResizeObserver' in window) {
    new ResizeObserver(() => {
        form.style.setProperty('--p-search-top-height', searchTop.offsetHeight + 'px');
    }).observe(searchTop);
}

/* ---------------------------------------------------------------------------
 * Map
 * ------------------------------------------------------------------------ */

function Map() {
    this.map = undefined;
    this.mapBox = document.getElementById('map-box');
    this.placeMarker = null;
}

Map.prototype.showMap = function () {
    if (this.map !== undefined) {
        this.map.invalidateSize();
        return;
    }

    const mapDiv = document.createElement('div');
    mapDiv.id = 'map';
    mapDiv.className = 'p-search__map-canvas';
    this.mapBox.appendChild(mapDiv);

    this.map = L.map('map', {
        center: [15, 0],
        zoomSnap: 0.5,
        zoomDelta: 1,
        maxZoom: 18,
        minZoom: 1,
        zoom: 2,
        zoomControl: false,
        fullscreenControl: true,
        fullscreenControlOptions: {
            position: 'topright'
        }
    });
    L.control.zoom({position: 'topright'}).addTo(this.map);
    // A light and plain base map, so that the members stand out
    addPositronLayer(this.map);
    this.markerClusterGroup = this.addMarkers(this.map);

    this.centerMap();
    const radius = document.getElementById('search_distance');
    const distance = radius ? parseInt(radius.value, 10) : NaN;
    if (this.placeMarker && '1' !== hidden('admin_unit').value && distance >= 0) {
        // A town: the map opens on the town itself (the search radius, between 3 and
        // 10 km around its centre), not on every member of the area; zooming out shows
        // the others.
        const halfSide = Math.min(Math.max(distance, 3), 10) * 1000;
        this.map.fitBounds(this.placeMarker.getLatLng().toBounds(2 * halfSide));
    } else if (this.markerClusterGroup.getLayers().length > 0) {
        // A country or a region: frame the members found, without going closer than a
        // district when they are all at one spot.
        this.map.fitBounds(this.markerClusterGroup.getBounds(), {padding: [48, 48], maxZoom: 13});
    }

    // Zooming or moving the map only changes the view; it doesn't search again.
};

Map.prototype.centerMap = function () {
    const latitude = hidden('latitude').value;
    const longitude = hidden('longitude').value;
    if ('' === latitude) {
        return;
    }

    const minLatitude = document.getElementById('min_latitude').value;
    const maxLatitude = document.getElementById('max_latitude').value;
    const minLongitude = document.getElementById('min_longitude').value;
    const maxLongitude = document.getElementById('max_longitude').value;

    // The searched place: not drawn (only the members are), but kept in the framing below
    this.placeMarker = L.marker([latitude, longitude], {
        icon: L.divIcon({className: 'p-search-map-place', iconSize: [16, 16]}),
        draggable: false,
        interactive: false
    });

    if (minLatitude !== '' && maxLatitude !== '') {
        const bounds = L.latLngBounds(L.latLng(minLatitude, minLongitude), L.latLng(maxLatitude, maxLongitude));
        this.map.fitBounds(bounds);
    } else {
        this.map.setView([latitude, longitude], 11);
    }
};

// A member picked in the list: the map shows their marker (out of its cluster), marked,
// in the middle of the part of the map that the preview drawer leaves free.
Map.prototype.focusMember = function (username) {
    this.unfocusMember();
    const marker = this.memberMarkers ? this.memberMarkers[username] : null;
    if (!this.map || !marker || this.map.getContainer().offsetParent === null) {
        return;
    }
    this.focused = marker;
    marker.setZIndexOffset(1000);
    this.markerClusterGroup.zoomToShowLayer(marker, () => {
        if (this.focused !== marker) {
            return;
        }
        const element = marker.getElement();
        if (element) {
            element.classList.add('is-focused');
        }
        // In the middle of the map, whatever the drawer covers
        const size = this.map.getSize();
        const point = this.map.latLngToContainerPoint(marker.getLatLng());
        this.map.panBy([point.x - size.x / 2, point.y - size.y / 2]);
    });
};

Map.prototype.unfocusMember = function () {
    if (this.focused) {
        const element = this.focused.getElement();
        if (element) {
            element.classList.remove('is-focused');
        }
        this.focused.setZIndexOffset(0);
        this.focused = null;
    }
};

Map.prototype.addMarkers = function (map) {
    this.memberMarkers = {};
    const markers = L.markerClusterGroup({
        showCoverageOnHover: false,
        iconCreateFunction: (cluster) => L.divIcon({
            iconSize: [48, 48],
            className: 'p-search-map-cluster',
            html: '<span>' + cluster.getChildCount() + '</span>'
        })
    });

    // Every member found (or, without that list, the members of this page): a click on the
    // marker opens the member's preview, as from the list.
    let points = [];
    if (typeof mapAllMembers !== 'undefined' && mapAllMembers !== null) {
        points = mapAllMembers;
    } else if (typeof mapMembers !== 'undefined' && mapMembers !== null) {
        points = mapMembers.map((value) => ({username: value.Username, latitude: value.latitude, longitude: value.longitude}));
    }
    points.forEach((value) => {
        const name = encodeURIComponent(value.username);
        const marker = L.marker([value.latitude, value.longitude], {
            title: value.username,
            icon: L.divIcon({
                iconSize: [36, 36],
                iconAnchor: [18, 18],
                // a green ring when the member hosts, a grey one when not
                className: 'p-search-map-member' + (value.hosting ? ' p-search-map-member--' + value.hosting : ''),
                html: '<img src="/members/avatar/' + name + '/48" alt="" loading="lazy">'
            })
        });
        marker.on('click', () => openPreviewUrl('/search/members/' + name + '/preview', null));
        markers.addLayer(marker);
        this.memberMarkers[value.username] = marker;
    });

    map.addLayer(markers);

    return markers;
};

const map = new Map();
const mapSwitch = document.querySelector('.js-search-map-switch');
const viewButtons = document.querySelectorAll('.js-search-view');

function showView(view) {
    form.classList.toggle('is-map-view', view === 'map');
    viewButtons.forEach((button) => {
        const active = button.dataset.view === view;
        button.classList.toggle('is-active', active);
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
    if (view === 'map') {
        map.showMap();
    }
}

viewButtons.forEach((button) => button.addEventListener('click', () => showView(button.dataset.view)));

// Large screens: the "Map" switch shows or hides the map next to the results.
function applyMapSwitch() {
    if (!mapSwitch || phone.matches) {
        return;
    }
    form.classList.toggle('is-map-off', !mapSwitch.checked);
    if (mapSwitch.checked) {
        map.showMap();
    }
}

if (mapSwitch) {
    mapSwitch.addEventListener('change', applyMapSwitch);
}

if (form) {
    applyMapSwitch();

    phone.addEventListener('change', () => {
        placeRadius();
        closeWhere();
        if (phone.matches) {
            if (form.classList.contains('is-map-view')) {
                map.showMap();
            }
        } else {
            applyMapSwitch();
        }
        if (map.map) {
            map.map.invalidateSize();
        }
    });
}

/* ---------------------------------------------------------------------------
 * Member preview (search/preview.js): the map shows the member opened from the list.
 * ------------------------------------------------------------------------ */

const {openPreviewUrl} = initMemberPreview({
    phone,
    onHide: () => map.unfocusMember(),
    onOpen: (username) => map.focusMember(username),
});

initializeTomSelects();

// The radius and the sort: a menu in the site's look instead of the browser's list
document.querySelectorAll('.p-search .p-search-select--radius, .p-search .p-search-select--small').forEach(prettySelect);
