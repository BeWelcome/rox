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
        updateFindButtons();
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

            // The popup is drawn by the page (search/_search.macros.twig, map_popup).
            marker.bindPopup(value.popup, {closeButton: false, minWidth: 220, maxWidth: 280, className: 'p-search-map-bubble'});

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

/* ---------------------------------------------------------------------------
 * Member preview: a click on a result opens a short profile over the results
 * (drawer on large screens, sheet on phones) instead of leaving the page.
 * The browser's back button closes it. Ctrl/Cmd/middle click still opens
 * the full profile in a new tab.
 * ------------------------------------------------------------------------ */

const preview = document.querySelector('.js-search-preview');
const previewBody = preview ? preview.querySelector('.js-search-preview-body') : null;
const previewBackdrop = document.querySelector('.p-search-preview__backdrop');
let previewRequest = null;
let previewOpenedHere = false;

function showPreview() {
    preview.classList.add('is-open');
    preview.setAttribute('aria-hidden', 'false');
    previewBackdrop.hidden = false;
    // Phones only: on large screens the results stay usable next to the drawer.
    document.body.classList.toggle('has-search-overlay', phone.matches);
}

function hidePreview() {
    preview.classList.remove('is-open');
    preview.setAttribute('aria-hidden', 'true');
    previewBackdrop.hidden = true;
    document.body.classList.remove('has-search-overlay');
    document.querySelectorAll('.p-search-card.is-previewed').forEach((card) => card.classList.remove('is-previewed'));
}

// The member's pictures at the top of the preview: swipe, or the arrows, with a "2 / 5" counter.
function initPreviewPhotos(root) {
    const photos = root.querySelector('.js-preview-photos');
    if (!photos) {
        return;
    }
    const track = photos.querySelector('.js-preview-photos-track');
    const prev = photos.querySelector('.js-preview-photos-prev');
    const next = photos.querySelector('.js-preview-photos-next');
    const counter = photos.querySelector('.js-preview-photos-counter');
    if (!prev || !next) {
        return;
    }
    const slides = track.children.length;
    const current = () => Math.round(track.scrollLeft / track.clientWidth);
    const update = () => {
        const index = current();
        prev.disabled = index <= 0;
        next.disabled = index >= slides - 1;
        if (counter) {
            counter.textContent = (index + 1) + ' / ' + counter.dataset.total;
        }
    };
    const go = (step) => track.scrollTo({left: (current() + step) * track.clientWidth, behavior: 'smooth'});
    prev.addEventListener('click', () => go(-1));
    next.addEventListener('click', () => go(1));
    track.addEventListener('scroll', () => window.requestAnimationFrame(update), {passive: true});
    update();
}

function loadPreview(url, card) {
    document.querySelectorAll('.p-search-card.is-previewed').forEach((other) => other.classList.remove('is-previewed'));
    if (card) {
        card.classList.add('is-previewed');
    }
    if (previewRequest) {
        previewRequest.abort();
    }
    previewRequest = new AbortController();
    previewBody.innerHTML = '<div class="p-search-preview__loading" aria-hidden="true"></div>';
    preview.querySelector('.p-search-preview__body').scrollTop = 0;
    showPreview();

    fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}, signal: previewRequest.signal})
        .then((response) => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.text();
        })
        .then((html) => {
            previewBody.innerHTML = html;
            initPreviewPhotos(previewBody);
        })
        .catch((error) => {
            if (error.name !== 'AbortError') {
                previewBody.innerHTML = '<p class="p-search-preview__error"></p>';
                previewBody.firstChild.textContent = preview.dataset.error;
            }
        });
}

// Closing goes back in the history when the preview added an entry to it.
function closePreview() {
    if (!preview.classList.contains('is-open')) {
        return;
    }
    if (previewOpenedHere && history.state && history.state.searchPreview) {
        history.back();
    } else {
        hidePreview();
    }
}

if (preview) {
    document.addEventListener('click', (event) => {
        const link = event.target.closest('.js-search-preview-open');
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        event.preventDefault();
        const state = {searchPreview: link.dataset.preview};
        if (history.state && history.state.searchPreview) {
            history.replaceState(state, '');
        } else {
            history.pushState(state, '');
            previewOpenedHere = true;
        }
        loadPreview(link.dataset.preview, link.closest('.p-search-card'));
    });

    window.addEventListener('popstate', (event) => {
        if (event.state && event.state.searchPreview) {
            loadPreview(event.state.searchPreview, null);
        } else {
            hidePreview();
        }
    });

    document.querySelectorAll('.js-search-preview-close').forEach((button) => button.addEventListener('click', closePreview));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closePreview();
        }
    });

    // Phones: drag the sheet down by its top bar to close it.
    const bar = preview.querySelector('.p-search-preview__bar');
    let startY = null;
    bar.addEventListener('touchstart', (event) => {
        startY = event.touches[0].clientY;
    }, {passive: true});
    bar.addEventListener('touchmove', (event) => {
        if (startY === null) {
            return;
        }
        const offset = Math.max(0, event.touches[0].clientY - startY);
        preview.style.transform = offset ? 'translateY(' + offset + 'px)' : '';
        preview.style.transition = 'none';
    }, {passive: true});
    bar.addEventListener('touchend', (event) => {
        if (startY === null) {
            return;
        }
        const offset = event.changedTouches[0].clientY - startY;
        startY = null;
        preview.style.transform = '';
        preview.style.transition = '';
        if (offset > 80) {
            closePreview();
        }
    });

    // Reloading the page on a preview entry opens it again.
    if (history.state && history.state.searchPreview) {
        loadPreview(history.state.searchPreview, null);
    }
}

initializeTomSelects();
