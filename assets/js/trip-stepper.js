/*
 * "Plan a public trip": the form in four steps (Where, About, Who, Review), one shown at a
 * time. Next checks the step before going on; the last step shows the trip as hosts will
 * see it, each part with Edit. Only when creating (.js-trip-stepper).
 */
const escapeHtml = (text) => String(text).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const parseDay = (value) => (/^\d{4}-\d{2}-\d{2}$/.test(value || '') ? new Date(value + 'T00:00:00Z') : null);

export function initTripStepper() {
    const form = document.querySelector('.js-trip-stepper');
    if (!form) {
        return;
    }
    const steps = Array.from(form.querySelectorAll('.p-trips-step[data-step]'));
    const total = steps.length;
    const nav = form.querySelector('.js-trip-steps');
    const items = nav ? Array.from(nav.querySelectorAll('.p-trips-steps__item')) : [];
    const sub = document.querySelector('.js-trip-step-sub');
    const scope = form.closest('.p-ped') || document;
    const backs = scope.querySelectorAll('.js-trip-back');
    const nexts = scope.querySelectorAll('.js-trip-next');
    const submits = scope.querySelectorAll('.js-trip-submit');
    const cancels = scope.querySelectorAll('.js-trip-cancel');
    const lang = document.documentElement.lang || undefined;
    const dayLabel = new Intl.DateTimeFormat(lang, { day: 'numeric', month: 'short', timeZone: 'UTC' });
    let current = 1;
    let reached = 1;

    const stepOf = (n) => steps.find((step) => step.dataset.step === String(n));

    // The required fields of a step are there (no marks): Next waits for it
    function complete(n) {
        const step = stepOf(n);
        if (n === 1) {
            const stops = Array.from(step.querySelectorAll('.p-trips-stop'));
            return stops.length > 0 && stops.every((stop) => {
                const place = stop.querySelector('.js-location-picker');
                const value = (selector) => (stop.querySelector(selector) || {}).value || '';
                return place && place.value.trim() !== '' && value('input[id$="_location_geoname_id"]') !== ''
                    && value('input[id$="_arrival"]') !== '' && value('input[id$="_departure"]') !== '';
            });
        }
        if (n === 2) {
            const summary = step.querySelector('input[name$="[summary]"]');
            return !summary || summary.value.trim() !== '';
        }
        return true;
    }

    function updateNext() {
        const ready = complete(current);
        nexts.forEach((button) => { button.disabled = !ready; });
    }

    // Where: each stop has a place picked in the suggestions and its dates
    // About: a title
    function check(n) {
        const step = stepOf(n);
        let first = null;
        if (n === 1) {
            step.querySelectorAll('.p-trips-stop').forEach((stop) => {
                const place = stop.querySelector('.js-location-picker');
                const geoname = stop.querySelector('input[id$="_location_geoname_id"]');
                const arrival = stop.querySelector('input[id$="_arrival"]');
                const departure = stop.querySelector('input[id$="_departure"]');
                const placeOk = place && place.value.trim() !== '' && geoname && geoname.value !== '';
                const datesOk = arrival && departure && arrival.value !== '' && departure.value !== '';
                place.closest('.p-trips-field').classList.toggle('has-error', !placeOk);
                const dates = stop.querySelector('.p-trips-field--dates');
                if (dates) {
                    dates.classList.toggle('has-error', !datesOk);
                }
                if (!first && !placeOk) {
                    first = place;
                } else if (!first && !datesOk) {
                    first = stop.querySelector('.js-duration') || place;
                }
            });
        } else if (n === 2) {
            const summary = step.querySelector('input[id$="_summary"], input[name$="[summary]"]');
            const ok = !summary || summary.value.trim() !== '';
            if (summary) {
                summary.closest('.p-trips-field').classList.toggle('has-error', !ok);
                if (!ok) {
                    first = summary;
                }
            }
        }
        if (first) {
            first.focus({ preventScroll: true });
            first.scrollIntoView({ block: 'center', behavior: 'smooth' });
            return false;
        }
        return true;
    }

    function fillReview() {
        const review = form.querySelector('.js-trip-review');
        if (!review) {
            return;
        }
        const plural = (count, one, other) => (count === 1 ? one : other.replace('{n}', count));
        const summary = form.querySelector('input[name$="[summary]"]');
        const description = form.querySelector('textarea[name$="[description]"]');
        review.querySelector('.js-review-summary').textContent = summary ? summary.value.trim() : '';
        const descriptionText = description ? description.value.trim() : '';
        const descriptionNode = review.querySelector('.js-review-description');
        descriptionNode.textContent = descriptionText;
        descriptionNode.hidden = descriptionText === '';

        let html = '';
        form.querySelectorAll('.p-trips-stop').forEach((stop, index) => {
            const place = stop.querySelector('.js-location-picker');
            const city = place ? place.value.split(',')[0].trim() : '';
            const start = parseDay((stop.querySelector('input[id$="_arrival"]') || {}).value);
            const end = parseDay((stop.querySelector('input[id$="_departure"]') || {}).value);
            let when = '';
            let nights = '';
            if (start && end) {
                const count = Math.max(0, Math.round((end - start) / 86400000));
                when = `${dayLabel.format(start)} – ${dayLabel.format(end)}`;
                nights = count > 0 ? plural(count, review.dataset.nightsOne, review.dataset.nightsOther) : '';
            }
            html += `<li class="p-trips-review__stop"><span class="p-trips-review__num">${index + 1}</span>`
                + `<span class="p-trips-review__stop-text"><strong>${escapeHtml(city)}</strong>`
                + (when ? `<span class="p-trips-review__when"><span>${escapeHtml(when)}</span>`
                    + (nights ? `<span class="p-trips-review__nights"><i class="fas fa-moon" aria-hidden="true"></i> ${escapeHtml(nights)}</span>` : '')
                    + '</span>' : '')
                + '</span></li>';
        });
        review.querySelector('.js-review-stops').innerHTML = html;

        const travellers = form.querySelector('.js-stepper-input');
        const count = travellers ? parseInt(travellers.value, 10) || 1 : 1;
        review.querySelector('.js-review-travellers').textContent = count >= 6
            ? review.dataset.travellersOther.replace('{n}', '5+')
            : plural(count, review.dataset.travellersOne, review.dataset.travellersOther);
        const optionText = (name) => {
            const select = form.querySelector(`select[name$="[${name}]"]`);
            return select && select.selectedIndex >= 0 ? select.options[select.selectedIndex].text.trim() : '';
        };
        review.querySelector('.js-review-radius').textContent = optionText('invitationRadius');
        review.querySelector('.js-review-info').textContent = optionText('additionalInfo');
    }

    function show(n, scroll = true) {
        current = n;
        reached = Math.max(reached, n);
        steps.forEach((step) => step.classList.toggle('is-current', step.dataset.step === String(n)));
        items.forEach((item, index) => {
            const go = index + 1;
            item.classList.toggle('is-current', go === n);
            item.classList.toggle('is-done', go < n);
            item.disabled = go > reached;
            if (go === n) {
                item.setAttribute('aria-current', 'step');
            } else {
                item.removeAttribute('aria-current');
            }
        });
        if (n === total) {
            fillReview();
        }
        if (sub && nav) {
            const label = items[n - 1] ? items[n - 1].querySelector('.p-trips-steps__label').textContent.trim() : '';
            sub.textContent = nav.dataset.of.replace('{n}', n) + (label ? ' · ' + label : '');
        }
        backs.forEach((button) => { button.hidden = n === 1; });
        cancels.forEach((button) => { button.hidden = n !== 1; });
        nexts.forEach((button) => { button.hidden = n === total; });
        submits.forEach((button) => { button.hidden = n !== total; });
        updateNext();
        if (scroll) {
            window.scrollTo({ top: 0 });
        }
    }

    function go(n) {
        if (n > current) {
            // Going on: every step on the way must be complete
            for (let i = current; i < n; i += 1) {
                if (!check(i)) {
                    show(i);
                    return;
                }
            }
        }
        show(n);
    }

    scope.addEventListener('click', (event) => {
        const target = event.target.closest('.js-trip-next, .js-trip-back, .js-trip-go');
        if (!target) {
            return;
        }
        event.preventDefault();
        if (target.classList.contains('js-trip-next')) {
            go(current + 1);
        } else if (target.classList.contains('js-trip-back')) {
            show(Math.max(1, current - 1));
        } else {
            go(parseInt(target.dataset.go, 10));
        }
    });

    // A field filled again: its red goes
    form.addEventListener('input', (event) => {
        const field = event.target.closest('.p-trips-field.has-error');
        if (field && event.target.value.trim() !== '') {
            field.classList.remove('has-error');
        }
    });
    form.addEventListener('change', (event) => {
        if (event.target.matches('input[id$="_departure"]') && event.target.value) {
            const dates = event.target.closest('.p-trips-stop').querySelector('.p-trips-field--dates');
            if (dates) {
                dates.classList.remove('has-error');
            }
        }
    });

    // Next follows the fields: typed, place picked, dates chosen, a stop added or removed
    // (after the page's own handlers: typing a place again empties its picked id)
    const later = () => window.setTimeout(updateNext, 0);
    form.addEventListener('input', later);
    form.addEventListener('change', later);
    form.addEventListener('click', later);
    new MutationObserver(updateNext).observe(stepOf(1), { childList: true, subtree: true });

    // Enter in a field goes to the next step, not straight to the server
    form.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && current < total && complete(current) && event.target.matches('input:not([type="checkbox"])')
            && !event.target.closest('.auto-search-wrapper')) {
            event.preventDefault();
            go(current + 1);
        }
    });
    form.addEventListener('submit', (event) => {
        if (current < total) {
            event.preventDefault();
            if (complete(current)) {
                go(current + 1);
            }
        }
    });

    // Back from the server with errors: open the first step that has one
    const errorStep = steps.find((step) => step.querySelector('.p-trips-stop__error, .invalid-feedback, .form-error-message, .has-error'));
    if (errorStep) {
        reached = total;
    }
    show(errorStep ? parseInt(errorStep.dataset.step, 10) : 1, false);
}
