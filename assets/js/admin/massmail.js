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

// Members tab: the usernames only count for "Selected members"
document.querySelectorAll('.js-massmail-recipients').forEach((form) => {
    const usernames = form.querySelector('.js-massmail-usernames');
    const choices = form.querySelectorAll('input[type=radio][name$="[members]"]');
    if (usernames && choices.length > 0) {
        const show = () => {
            const chosen = form.querySelector('input[type=radio][name$="[members]"]:checked');
            usernames.hidden = chosen !== null && chosen.value !== 'usernames';
        };
        choices.forEach((choice) => choice.addEventListener('change', show));
        show();
    }

    // Place and group: Enqueue once the country or group is chosen
    const required = form.querySelector('select.js-massmail-required');
    const submit = form.querySelector('.js-massmail-enqueue');
    if (required && submit) {
        const enable = () => {
            submit.disabled = required.value === '';
        };
        required.addEventListener('change', enable);
        enable();
    }
});

// "About N members" next to Enqueue: the server counts the members the chosen recipients are
document.querySelectorAll('.js-massmail-estimate').forEach((estimate) => {
    const form = estimate.closest('form');
    const { recipients, url } = estimate.dataset;
    let timer = null;
    let latest = 0;

    const update = () => {
        const query = new URLSearchParams({ recipients });
        Array.from(form.elements).forEach((field) => {
            const name = (field.name || '').match(new RegExp(`^${recipients}\\[(\\w+)\\]$`));
            if (!name || ['_token', 'enqueue'].includes(name[1]) || field.value === '') {
                return;
            }
            if (field.type === 'radio' && !field.checked) {
                return;
            }
            query.set(name[1], field.value);
        });
        latest += 1;
        const request = latest;
        fetch(`${url}?${query}`, { credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then((response) => (response.ok ? response.json() : null))
            .then((result) => {
                // only the answer to the latest question counts
                if (request === latest) {
                    estimate.textContent = result && result.count > 0 ? result.text : '';
                }
            })
            .catch(() => {
                estimate.textContent = '';
            });
    };
    const later = () => {
        clearTimeout(timer);
        timer = setTimeout(update, 300);
    };

    form.addEventListener('input', later);
    form.addEventListener('change', later);
    update();
});
