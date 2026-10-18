import {initializeSingleAutoComplete} from '../suggest/locations';
import {initializeTomSelects} from '../tom-select';

import L from 'leaflet';
import 'leaflet.fullscreen';
import 'leaflet.fullscreen/Control.FullScreen.css';
import 'leaflet.markercluster';
import 'leaflet.markercluster/dist/MarkerCluster.Default.css';
import 'leaflet.markercluster/dist/MarkerCluster.css';
import 'leaflet/dist/leaflet.css';

const form = document.querySelector('.js-search-form');
const phone = window.matchMedia('(max-width: 767.98px)');

/* ---------------------------------------------------------------------------
 * Where are you going?
 * ------------------------------------------------------------------------ */

const where = document.querySelector('.js-search-where');
const locationInput = document.getElementById('search_location');
const hidden = (name) => document.getElementById('search_location_' + name);

// Shows "Berlin  Land Berlin, Germany" over the field while it is not being edited.
function updateWhereDisplay() {
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
    updateWhereDisplay();

    // On phones picking a place is the search: go straight to the results.
    if (phone.matches) {
        closeWhere();
        submit();
    }
}

initializeSingleAutoComplete('/suggest/locations/all', 'js-location-picker', onChange);

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

if (where && locationInput) {
    locationInput.addEventListener('focus', () => {
        where.classList.add('is-editing');
        openWhere();
    });
    locationInput.addEventListener('blur', () => {
        where.classList.remove('is-editing');
        updateWhereDisplay();
    });
    // A new text is a new place: it has to be picked from the list again.
    locationInput.addEventListener('input', () => {
        ['geoname_id', 'latitude', 'longitude', 'admin_unit'].forEach((name) => { hidden(name).value = ''; });
    });
    where.querySelector('.js-search-where-back').addEventListener('click', () => {
        locationInput.blur();
        closeWhere();
    });
    // The clear button of the suggestion box empties the hidden fields too.
    where.addEventListener('click', (event) => {
        if (event.target.closest('.auto-clear')) {
            ['fullname', 'name', 'geoname_id', 'latitude', 'longitude', 'admin_unit'].forEach((name) => { hidden(name).value = ''; });
            updateWhereDisplay();
        }
    });
    updateWhereDisplay();
}

/* ---------------------------------------------------------------------------
 * Searching
 * ------------------------------------------------------------------------ */

function submit() {
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

    // Back to the default options; the search runs when "Find members" is pressed.
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
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap contributors</a>',
        subdomains: ['a', 'b', 'c']
    }).addTo(this.map);
    this.markerClusterGroup = this.addMarkers(this.map);

    if (this.markerClusterGroup.getLayers().length > 0) {
        if ('1' === hidden('admin_unit').value) {
            // A region: show all its members.
            this.map.fitBounds(this.markerClusterGroup.getBounds(), {padding: [40, 40]});
        } else {
            this.centerMap();
        }
    } else {
        this.centerMap();
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

    L.marker([latitude, longitude], {
        icon: L.divIcon({className: 'p-search-map-place', iconSize: [16, 16]}),
        draggable: false,
        interactive: false
    }).addTo(this.map);

    if (minLatitude !== '' && maxLatitude !== '') {
        const bounds = L.latLngBounds(L.latLng(minLatitude, minLongitude), L.latLng(maxLatitude, maxLongitude));
        this.map.fitBounds(bounds);
    } else {
        this.map.setView([latitude, longitude], 11);
    }
};

Map.prototype.addMarkers = function (map) {
    const markers = L.markerClusterGroup({
        showCoverageOnHover: false,
        iconCreateFunction: (cluster) => L.divIcon({
            iconSize: [48, 48],
            className: 'p-search-map-cluster',
            html: '<span>' + cluster.getChildCount() + '</span>'
        })
    });

    if (typeof mapMembers !== 'undefined' && mapMembers !== null) {
        mapMembers.forEach((value) => {
            const marker = L.marker([value.latitude, value.longitude], {
                icon: L.divIcon({
                    iconSize: [36, 36],
                    iconAnchor: [18, 18],
                    className: 'p-search-map-member',
                    html: '<img src="/members/avatar/' + encodeURIComponent(value.Username) + '/48" alt="">'
                })
            });

            const popup = document.createElement('a');
            popup.className = 'p-search-map-popup';
            popup.href = '/members/' + encodeURIComponent(value.Username);
            const avatar = document.createElement('img');
            avatar.src = '/members/avatar/' + encodeURIComponent(value.Username) + '/48';
            avatar.alt = '';
            const name = document.createElement('strong');
            name.textContent = value.Username;
            const guests = document.createElement('span');
            guests.innerHTML = '<i class="fas fa-bed" aria-hidden="true"></i> ';
            guests.appendChild(document.createTextNode(value.MaxGuests));
            popup.append(avatar, name, guests);
            marker.bindPopup(popup, {closeButton: false, minWidth: 90, maxWidth: 220});

            markers.addLayer(marker);
        });
    }

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

initializeTomSelects();
