// The countries and groups to choose from are long lists: tom-select (it starts by itself)
import '../tom-select';

// The administrative units of the chosen country and the places of the chosen unit are loaded from the server.
const country = document.querySelector('[data-url-admin-units]');
const adminUnit = document.querySelector('.js-massmail-admin-unit');
const place = document.querySelector('.js-massmail-place');

function reset(select) {
    select.querySelectorAll('option:not(:first-child)').forEach((option) => option.remove());
    select.value = '';
    select.disabled = true;
}

function fill(select, url) {
    fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
        .then((response) => response.json())
        .then((items) => {
            items.forEach((item) => select.add(new Option(item.name, item.id)));
            select.disabled = items.length === 0;
        });
}

if (country && adminUnit && place) {
    country.addEventListener('change', () => {
        reset(adminUnit);
        reset(place);
        if (country.value !== '') {
            fill(adminUnit, country.dataset.urlAdminUnits.replace('XX', country.value));
        }
    });
    adminUnit.addEventListener('change', () => {
        reset(place);
        if (adminUnit.value !== '') {
            fill(place, country.dataset.urlPlaces.replace('XX', country.value).replace('YY', encodeURIComponent(adminUnit.value)));
        }
    });
}
