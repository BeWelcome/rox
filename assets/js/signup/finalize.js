import L from 'leaflet';
import { initializeSingleAutoComplete } from '../suggest/locations';
import { initializeCalendar } from '../calendar';
import { addPositronLayer } from '../map/positron';

/*
 * Finish the account (templates/signup/finalize.html.twig): the birth date calendar, the
 * mother tongues (chips, search and common languages), the town with its map and pin
 * (as in "Set location"), "Can you host?" and the hosting interest on 10 steps.
 */

initializeCalendar('signup_form_finalize_birthdate');

// ── Where you live: the search places the pin, the pin can be dragged ──
const geonameId = document.getElementById('signup_form_finalize_location_geoname_id');
const latitude = document.getElementById('signup_form_finalize_location_latitude');
const longitude = document.getElementById('signup_form_finalize_location_longitude');
const hint = document.querySelector('.js-location-hint');
const hintText = document.querySelector('.js-location-hint-text');
const markerLabel = document.getElementById('marker_label_text').value;

const pinIcon = L.divIcon({
    className: 'p-location-pin',
    html: '<span class="p-location-pin__shadow"></span><span class="p-location-pin__shape"></span>',
    iconSize: [48, 56],
    iconAnchor: [24, 52],
    popupAnchor: [0, -50],
});

const map = L.map('map', { scrollWheelZoom: false });
addPositronLayer(map);
L.control.scale().addTo(map);

let marker = null;
const placePin = (lat, lng) => {
    if (marker) {
        map.removeLayer(marker);
    }
    marker = L.marker([lat, lng], { icon: pinIcon, draggable: true });
    marker.bindPopup(markerLabel);
    marker.on('dragend', (event) => {
        latitude.value = event.target.getLatLng().lat;
        longitude.value = event.target.getLatLng().lng;
        hint.classList.add('is-done');
    });
    marker.addTo(map);
    map.setView([lat, lng], 12, { animate: true });
    hintText.textContent = hint.dataset.picked;
    hint.classList.remove('is-done');
};

// Sent back with an error: the place picked before
if (latitude.value !== '' && longitude.value !== '' && geonameId.value !== '') {
    placePin(latitude.value, longitude.value);
} else {
    map.setView([50, 10], 3);
}

initializeSingleAutoComplete('/suggest/locations/places/exact', 'js-location-picker', (element, result) => {
    geonameId.value = result.id;
    latitude.value = result.latitude;
    longitude.value = result.longitude;
    placePin(result.latitude, result.longitude);
});

// ── Can you host? The hosting interest shows for "yes" only ──
const interest = document.querySelector('.js-sf-interest');
document.querySelectorAll('.js-sf-host').forEach((radio) => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.js-sf-host').forEach((other) => {
            other.closest('.p-pe-host__option').classList.toggle('is-active', other.checked);
        });
        interest.hidden = radio.value !== 'yes' || !radio.checked;
    });
});

// ── Hosting interest: 10 steps, the name of the one picked above them ──
if (interest) {
    const names = JSON.parse(interest.dataset.names || '[]');
    const value = interest.querySelector('.js-sf-interest-value');
    const name = interest.querySelector('.js-sf-interest-name');
    const score = interest.querySelector('.js-sf-interest-score');
    const steps = interest.querySelectorAll('.js-sf-interest-step');
    const show = (picked) => {
        value.value = picked;
        name.textContent = names[picked] || '';
        name.classList.toggle('is-empty', picked === 0);
        score.textContent = picked + ' / 10';
        steps.forEach((step) => {
            const stepValue = parseInt(step.dataset.value, 10);
            step.classList.toggle('is-on', stepValue <= picked);
            step.classList.toggle('is-picked', stepValue === picked);
            step.setAttribute('aria-checked', stepValue === picked ? 'true' : 'false');
        });
    };
    steps.forEach((step) => step.addEventListener('click', () => show(parseInt(step.dataset.value, 10))));
}

// ── Mother tongues: chips filled from the hidden list of the form ──
const tongues = document.querySelector('.js-sf-tongues');
if (tongues) {
    const select = tongues.querySelector('.js-sf-tongues-select');
    const chips = tongues.querySelector('.js-sf-tongues-chips');
    const search = tongues.querySelector('.js-lang-search');
    const results = tongues.querySelector('.js-lang-results');
    const commons = tongues.querySelectorAll('.js-lang-common');
    const languages = JSON.parse(tongues.dataset.languages || '[]');
    const removeLabel = tongues.dataset.remove || '';

    const escapeHtml = (text) => String(text)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const picked = () => new Set(Array.from(select.selectedOptions).map((option) => option.value));

    const render = () => {
        const codes = picked();
        chips.innerHTML = Array.from(select.selectedOptions).map((option) => `
            <span class="p-sf-chip">
                ${escapeHtml(option.textContent)}
                <button type="button" class="p-sf-chip__remove js-sf-tongue-remove" data-code="${escapeHtml(option.value)}"
                        aria-label="${escapeHtml(removeLabel)}" title="${escapeHtml(removeLabel)}">
                    <i class="fas fa-times" aria-hidden="true"></i>
                </button>
            </span>`).join('');
        chips.hidden = codes.size === 0;
        commons.forEach((chip) => {
            chip.hidden = codes.has(chip.dataset.code);
        });
    };

    const setPicked = (code, on) => {
        const option = Array.from(select.options).find((item) => item.value === code);
        if (option) {
            option.selected = on;
            render();
        }
    };

    chips.addEventListener('click', (event) => {
        const remove = event.target.closest('.js-sf-tongue-remove');
        if (remove) {
            setPicked(remove.dataset.code, false);
        }
    });
    commons.forEach((chip) => chip.addEventListener('click', () => setPicked(chip.dataset.code, true)));

    // The search: the first languages whose name starts with, then contains, what is typed
    let matches = [];
    let active = 0;
    const showResults = () => {
        results.innerHTML = matches.map((item, position) => `
            <li role="option" class="p-lang-add__result${position === active ? ' is-active' : ''}" data-code="${escapeHtml(item.code)}" aria-selected="${position === active}">
                ${escapeHtml(item.name)}
            </li>`).join('');
        results.hidden = matches.length === 0;
    };
    const find = () => {
        const text = search.value.trim().toLowerCase();
        const codes = picked();
        active = 0;
        if (!text) {
            matches = [];
        } else {
            const free = languages.filter((item) => !codes.has(item.code));
            const starts = free.filter((item) => item.name.toLowerCase().startsWith(text));
            const contains = free.filter((item) => !item.name.toLowerCase().startsWith(text) && item.name.toLowerCase().includes(text));
            matches = starts.concat(contains).slice(0, 8);
        }
        showResults();
    };
    const pick = (code) => {
        setPicked(code, true);
        search.value = '';
        matches = [];
        showResults();
    };
    search.addEventListener('input', find);
    search.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' && matches.length) {
            event.preventDefault();
            active = (active + 1) % matches.length;
            showResults();
        } else if (event.key === 'ArrowUp' && matches.length) {
            event.preventDefault();
            active = (active - 1 + matches.length) % matches.length;
            showResults();
        } else if (event.key === 'Enter') {
            // Never sends the form from the search
            event.preventDefault();
            if (matches[active]) {
                pick(matches[active].code);
            }
        } else if (event.key === 'Escape') {
            matches = [];
            showResults();
        }
    });
    results.addEventListener('mousedown', (event) => {
        const item = event.target.closest('[data-code]');
        if (item) {
            event.preventDefault();
            pick(item.dataset.code);
        }
    });
    search.addEventListener('blur', () => {
        matches = [];
        showResults();
    });

    render();
}

// Sent back with errors: the first one in view
const firstError = document.querySelector('.p-sf .invalid-feedback, .p-sf .alert-danger');
if (firstError) {
    firstError.closest('.p-sf-field, .p-sf-interest, .p-pe-card, form').scrollIntoView({ block: 'center' });
}
