/*
 * The dates of a stay in the side panel (stay request, invitation): a calendar always
 * open while the dates are being picked. A first tap picks the arrival, a second one the
 * departure (or a "1 night... 1 week" shortcut), then the dates fold into one line with
 * "Change". The form keeps its own arrival and departure fields (hidden here): they are
 * filled once both dates are picked, and emptied while a new stay is being picked.
 *
 * root: the .js-stay-dates element, with the texts in data attributes.
 */
const DAY = 24 * 60 * 60 * 1000;
const parseDay = (value) => (value ? new Date(value + 'T00:00:00Z') : null);
const formatDay = (date) => date.toISOString().slice(0, 10);
const addDays = (date, days) => new Date(date.getTime() + days * DAY);

const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

export function initStayDates(root, {arrival, departure, hide = []}) {
    const lang = document.documentElement.lang || 'en';
    const texts = root.dataset;
    const utc = { timeZone: 'UTC' };
    const dayLabel = new Intl.DateTimeFormat(lang, { ...utc, weekday: 'short', day: 'numeric', month: 'short' });
    const monthLabel = new Intl.DateTimeFormat(lang, { ...utc, month: 'long', year: 'numeric' });
    const weekdayLabel = new Intl.DateTimeFormat(lang, { ...utc, weekday: 'narrow' });
    const plural = new Intl.PluralRules(lang);
    const nightsLabel = (count) => (plural.select(count) === 'one' ? texts.one : texts.other).replace('{n}', count);

    const now = new Date();
    const today = parseDay(arrival.min) || new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()));

    let start = parseDay(arrival.value);
    let end = start ? parseDay(departure.value) : null;
    if (end && end <= start) {
        end = null;
    }
    // What the next tap picks: "arrival" or "departure"
    let picking = start ? 'departure' : 'arrival';
    let open = !(start && end);
    let month = new Date(Date.UTC((start || today).getUTCFullYear(), (start || today).getUTCMonth(), 1));

    hide.forEach((element) => { element.style.display = 'none'; });

    // The form's fields: filled once the stay is picked, empty while it is being picked
    const save = () => {
        arrival.value = start && end ? formatDay(start) : '';
        departure.value = start && end ? formatDay(end) : '';
        [arrival, departure].forEach((input) => {
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    };

    const cell = (kind, date) => `
        <button type="button" class="c-stay-dates__cell${picking === kind ? ' is-active' : ''}${date ? ' is-filled' : ''}" data-pick="${kind}">
            <span class="c-stay-dates__cell-label">${escapeHtml(kind === 'arrival' ? texts.arrival : texts.departure)}</span>
            <span class="c-stay-dates__cell-value">${escapeHtml(date ? dayLabel.format(date) : texts.select)}</span>
        </button>`;

    const calendar = () => {
        const first = month;
        const offset = (first.getUTCDay() + 6) % 7; // Monday first
        const days = new Date(Date.UTC(first.getUTCFullYear(), first.getUTCMonth() + 1, 0)).getUTCDate();
        const canGoBack = first > today;
        let weekdays = '';
        for (let i = 0; i < 7; i++) {
            weekdays += `<span class="c-stay-dates__weekday">${escapeHtml(weekdayLabel.format(addDays(new Date(Date.UTC(2024, 0, 1)), i)))}</span>`;
        }
        let grid = '<span></span>'.repeat(offset);
        for (let d = 1; d <= days; d++) {
            const date = new Date(Date.UTC(first.getUTCFullYear(), first.getUTCMonth(), d));
            const time = date.getTime();
            const classes = ['c-stay-dates__day'];
            const past = date < today;
            if (past) classes.push('is-past');
            if (time === today.getTime()) classes.push('is-today');
            if (start && time === start.getTime()) classes.push('is-start');
            if (end && time === end.getTime()) classes.push('is-end');
            if (start && end && date > start && date < end) classes.push('is-between');
            if (start && end) {
                if (time === start.getTime()) classes.push('has-range');
                if (time === end.getTime()) classes.push('has-range');
            }
            grid += `<button type="button" class="${classes.join(' ')}" data-day="${formatDay(date)}"${past ? ' disabled' : ''}><span>${d}</span></button>`;
        }
        return `
            <div class="c-stay-dates__month">
                <button type="button" class="c-stay-dates__nav" data-month="-1" aria-label="${escapeHtml(texts.previous)}"${canGoBack ? '' : ' disabled'}><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                <span class="c-stay-dates__title">${escapeHtml(monthLabel.format(first))}</span>
                <button type="button" class="c-stay-dates__nav" data-month="1" aria-label="${escapeHtml(texts.next)}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
            </div>
            <div class="c-stay-dates__grid">${weekdays}${grid}</div>`;
    };

    const render = () => {
        if (!open && start && end) {
            const count = Math.round((end - start) / DAY);
            root.innerHTML = `
                <button type="button" class="c-stay-dates__summary" data-change>
                    <span class="c-stay-dates__summary-icon"><i class="fas fa-calendar-check" aria-hidden="true"></i></span>
                    <span class="c-stay-dates__summary-text">
                        <span class="c-stay-dates__summary-range">${escapeHtml(dayLabel.format(start))} <i class="fas fa-arrow-right" aria-hidden="true"></i> ${escapeHtml(dayLabel.format(end))}</span>
                        <span class="c-stay-dates__summary-nights"><i class="fas fa-moon" aria-hidden="true"></i> ${escapeHtml(nightsLabel(count))}</span>
                    </span>
                    <span class="c-stay-dates__change">${escapeHtml(texts.change)}</span>
                </button>`;
            return;
        }
        let help = `<p class="c-stay-dates__help">${escapeHtml(texts.tapArrival)}</p>`;
        if (picking === 'departure' && start) {
            const shortcuts = [1, 2, 3, 7].map((count) => `<button type="button" class="c-stay-dates__chip" data-nights="${count}">${escapeHtml(count === 7 ? texts.week : nightsLabel(count))}</button>`).join('');
            help = `<p class="c-stay-dates__help c-stay-dates__help--left">${escapeHtml(texts.tapDeparture)}</p><div class="c-stay-dates__chips">${shortcuts}</div>`;
        }
        root.innerHTML = `
            <div class="c-stay-dates__cells">${cell('arrival', start)}${cell('departure', end)}</div>
            ${calendar()}
            ${help}`;
    };

    root.addEventListener('click', (event) => {
        const target = event.target.closest('button');
        if (!target || !root.contains(target)) {
            return;
        }
        if (target.hasAttribute('data-change')) {
            open = true;
            picking = 'arrival';
            month = new Date(Date.UTC(start.getUTCFullYear(), start.getUTCMonth(), 1));
        } else if (target.dataset.pick) {
            picking = target.dataset.pick === 'departure' && start ? 'departure' : 'arrival';
        } else if (target.dataset.month) {
            month = new Date(Date.UTC(month.getUTCFullYear(), month.getUTCMonth() + parseInt(target.dataset.month, 10), 1));
        } else if (target.dataset.day) {
            const day = parseDay(target.dataset.day);
            if (picking === 'departure' && start && day > start) {
                end = day;
                open = false;
            } else {
                // A new stay: this day is the arrival
                start = day;
                end = null;
                picking = 'departure';
            }
            save();
        } else if (target.dataset.nights && start) {
            end = addDays(start, parseInt(target.dataset.nights, 10));
            open = false;
            save();
        }
        render();
    });

    render();
}
