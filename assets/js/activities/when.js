/*
 * When the activity takes place: a date, a start time and an end time, each picked from a
 * button that opens a calendar or a list of times, instead of the browser's date and time
 * fields. The end time list shows how long the activity lasts and may run past midnight.
 * "Lasts several days" adds an end date. The form keeps its own start and end fields
 * (hidden here) and they are filled on every change. Without JavaScript they show as before.
 *
 * root: the .js-activity-when element, with the texts in data attributes.
 */
const DAY = 24 * 60;
const STEP = 15;

const pad = (value) => String(value).padStart(2, '0');
const parseDay = (value) => new Date(value + 'T00:00:00Z');
const formatDay = (date) => date.toISOString().slice(0, 10);
const addDays = (date, days) => new Date(date.getTime() + days * DAY * 60000);
const daysBetween = (from, to) => Math.round((to.getTime() - from.getTime()) / (DAY * 60000));

const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

const icons = {
    calendar: '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="5" width="17" height="15.5" rx="3"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg>',
    clock: '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/></svg>',
    chevron: '<svg class="p-when__chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>',
    left: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m14 6-6 6 6 6"/></svg>',
    right: '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m10 6 6 6-6 6"/></svg>',
};

// "2026-11-22T19:00" → { day, minutes }
function readField(input) {
    const match = /^(\d{4}-\d{2}-\d{2})T(\d{2}):(\d{2})/.exec(input.value || '');
    return match ? { day: parseDay(match[1]), minutes: Number(match[2]) * 60 + Number(match[3]) } : null;
}

export function initActivityWhen(root) {
    const starts = root.querySelector('input[name$="[starts]"]');
    const ends = root.querySelector('input[name$="[ends]"]');
    const fields = root.querySelector('.js-when-fields');
    if (!starts || !ends || !fields) {
        return;
    }
    const texts = root.dataset;
    const lang = document.documentElement.lang || 'en';
    const utc = { timeZone: 'UTC' };
    const dayLabel = new Intl.DateTimeFormat(lang, { ...utc, weekday: 'short', day: 'numeric', month: 'short' });
    const monthLabel = new Intl.DateTimeFormat(lang, { ...utc, month: 'long', year: 'numeric' });
    const weekdayLabel = new Intl.DateTimeFormat(lang, { ...utc, weekday: 'narrow' });
    const timeFormat = new Intl.DateTimeFormat(lang, { ...utc, hour: '2-digit', minute: '2-digit' });
    const timeLabel = (minutes) => timeFormat.format(new Date(Date.UTC(2024, 0, 1, 0, ((minutes % DAY) + DAY) % DAY)));
    const durationLabel = (minutes) => {
        const hours = Math.floor(minutes / 60);
        const rest = minutes % 60;
        if (typeof Intl.DurationFormat === 'function') {
            return new Intl.DurationFormat(lang, { style: 'narrow' }).format({ hours, minutes: rest });
        }
        return hours ? `${hours} h${rest ? ' ' + pad(rest) : ''}` : `${rest} min`;
    };

    const now = new Date();
    const today = new Date(Date.UTC(now.getFullYear(), now.getMonth(), now.getDate()));

    // The activity: start day and time, end as minutes after the start day's midnight
    const start = readField(starts);
    const end = readField(ends);
    let day = start ? start.day : null;
    let from = start ? start.minutes : 19 * 60;
    let until = start && end ? daysBetween(start.day, end.day) * DAY + end.minutes : from + 2 * 60;
    if (until <= from) {
        until = from + 2 * 60;
    }
    let several = until >= from + DAY;
    const first = day && day < today ? day : today;
    let month = new Date(Date.UTC((day || today).getUTCFullYear(), (day || today).getUTCMonth(), 1));
    let open = null;

    fields.style.display = 'none';

    const save = () => {
        starts.value = day ? `${formatDay(day)}T${pad(Math.floor(from / 60))}:${pad(from % 60)}` : '';
        const endDay = day ? addDays(day, Math.floor(until / DAY)) : null;
        const endMinutes = until % DAY;
        ends.value = day ? `${formatDay(endDay)}T${pad(Math.floor(endMinutes / 60))}:${pad(endMinutes % 60)}` : '';
    };

    const ui = document.createElement('div');
    ui.className = 'p-when';
    fields.before(ui);

    const button = (kind, icon, label, value, empty = false) => `
        <div class="p-when__item p-when__item--${kind}">
            <span class="p-activities-label">${escapeHtml(label)}</span>
            <button type="button" class="p-when__btn${empty ? ' is-empty' : ''}${open === kind ? ' is-open' : ''}" data-open="${kind}" aria-expanded="${open === kind}">
                ${icon}<span class="p-when__value">${escapeHtml(value)}</span>${icons.chevron}
            </button>
        </div>`;

    const calendar = (selected, min) => {
        const offset = (month.getUTCDay() + 6) % 7; // Monday first
        const days = new Date(Date.UTC(month.getUTCFullYear(), month.getUTCMonth() + 1, 0)).getUTCDate();
        let weekdays = '';
        for (let i = 0; i < 7; i++) {
            weekdays += `<span class="p-when__weekday">${escapeHtml(weekdayLabel.format(addDays(new Date(Date.UTC(2024, 0, 1)), i)))}</span>`;
        }
        let grid = '<span></span>'.repeat(offset);
        for (let d = 1; d <= days; d++) {
            const date = new Date(Date.UTC(month.getUTCFullYear(), month.getUTCMonth(), d));
            const classes = ['p-when__day'];
            if (date.getTime() === today.getTime()) classes.push('is-today');
            if (selected && date.getTime() === selected.getTime()) classes.push('is-selected');
            grid += `<button type="button" class="${classes.join(' ')}" data-day="${formatDay(date)}"${date < min ? ' disabled' : ''}>${d}</button>`;
        }
        return `
            <div class="p-when__cal">
                <div class="p-when__cal-head">
                    <button type="button" class="p-when__nav" data-month="-1" aria-label="${escapeHtml(texts.previous)}"${month <= new Date(Date.UTC(min.getUTCFullYear(), min.getUTCMonth(), 1)) ? ' disabled' : ''}>${icons.left}</button>
                    <span class="p-when__month">${escapeHtml(monthLabel.format(month))}</span>
                    <button type="button" class="p-when__nav" data-month="1" aria-label="${escapeHtml(texts.next)}">${icons.right}</button>
                </div>
                <div class="p-when__grid">${weekdays}${grid}</div>
            </div>`;
    };

    // Times every 15 minutes; the end list shows how long it lasts and goes past midnight
    const times = (kind) => {
        let options = [];
        if (kind === 'from') {
            for (let m = 0; m < DAY; m += STEP) options.push(m);
            if (!options.includes(from)) options.push(from);
        } else if (kind === 'until') {
            for (let m = from + STEP; m < from + DAY; m += STEP) options.push(m);
            if (!options.includes(until) && until > from && until < from + DAY) options.push(until);
        } else {
            const base = Math.floor(until / DAY) * DAY;
            for (let m = 0; m < DAY; m += STEP) options.push(base + m);
            if (!options.includes(until)) options.push(until);
        }
        options.sort((a, b) => a - b);
        const current = kind === 'from' ? from : until;
        return `<ul class="p-when__times" role="listbox">${options.map((m) => {
            let extra = '';
            if (kind === 'until') {
                extra = `<span class="p-when__lasts">${escapeHtml(durationLabel(m - from))}${m >= DAY ? ' · ' + escapeHtml(texts.nextDay) : ''}</span>`;
            }
            return `<li><button type="button" role="option" class="p-when__time${m === current ? ' is-selected' : ''}" data-minutes="${m}" aria-selected="${m === current}">${escapeHtml(timeLabel(m))}${extra}</button></li>`;
        }).join('')}</ul>`;
    };

    const endDay = () => (day ? addDays(day, Math.floor(until / DAY)) : null);

    const render = () => {
        const summary = day
            ? (several
                ? `${dayLabel.format(day)}, ${timeLabel(from)} – ${dayLabel.format(endDay())}, ${timeLabel(until)}`
                : `${dayLabel.format(day)} · ${timeLabel(from)} – ${timeLabel(until)}${until >= DAY ? ' (' + texts.nextDay + ')' : ''} · ${durationLabel(until - from)}`)
            : '';
        let popover = '';
        if (open === 'date') popover = calendar(day, first);
        if (open === 'end-date') popover = calendar(endDay(), day ? addDays(day, 1) : first);
        if (open === 'from' || open === 'until' || open === 'until-day') popover = times(open);

        ui.innerHTML = `
            <div class="p-when__row${several ? ' p-when__row--several' : ''}">
                ${button('date', icons.calendar, several ? texts.startDate : texts.date, day ? dayLabel.format(day) : texts.pick, !day)}
                ${button('from', icons.clock, texts.from, timeLabel(from))}
                ${several ? button('end-date', icons.calendar, texts.endDate, day ? dayLabel.format(endDay()) : texts.pick, !day) : ''}
                ${button(several ? 'until-day' : 'until', icons.clock, texts.until, timeLabel(until))}
            </div>
            ${popover ? `<div class="p-when__pop p-when__pop--${open}">${popover}</div>` : ''}
            <div class="p-when__foot">
                <label class="p-activities-option p-when__several">
                    <input type="checkbox" class="js-when-several"${several ? ' checked' : ''}>
                    <span>${escapeHtml(texts.several)}</span>
                </label>
                ${summary ? `<p class="p-when__summary">${escapeHtml(summary)}</p>` : ''}
            </div>`;

        const pop = ui.querySelector('.p-when__pop');
        if (pop) {
            const anchor = ui.querySelector(`[data-open="${open}"]`).closest('.p-when__item');
            const wide = window.matchMedia('(min-width: 768px)').matches;
            pop.style.top = `${anchor.offsetTop + anchor.offsetHeight + 6}px`;
            if (wide) {
                pop.style.left = `${Math.max(0, Math.min(anchor.offsetLeft, ui.clientWidth - pop.offsetWidth))}px`;
            }
            const selected = pop.querySelector('.p-when__time.is-selected');
            if (selected) {
                pop.querySelector('.p-when__times').scrollTop = selected.offsetTop - 2 * selected.offsetHeight;
            }
        }
    };

    ui.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-open]');
        if (opener) {
            open = open === opener.dataset.open ? null : opener.dataset.open;
            if (open === 'date' || open === 'end-date') {
                const shown = (open === 'date' ? day : endDay()) || day || today;
                month = new Date(Date.UTC(shown.getUTCFullYear(), shown.getUTCMonth(), 1));
            }
            render();
            return;
        }
        const nav = event.target.closest('[data-month]');
        if (nav) {
            month = new Date(Date.UTC(month.getUTCFullYear(), month.getUTCMonth() + Number(nav.dataset.month), 1));
            render();
            return;
        }
        const picked = event.target.closest('[data-day]');
        if (picked) {
            const date = parseDay(picked.dataset.day);
            if (open === 'date') {
                // The end moves with the start day
                day = date;
                // A first date for a new activity: the start time comes next
                open = starts.value ? null : 'from';
            } else {
                until = Math.max(1, daysBetween(day, date)) * DAY + (until % DAY);
                open = null;
            }
            save();
            render();
            return;
        }
        const time = event.target.closest('[data-minutes]');
        if (time) {
            const minutes = Number(time.dataset.minutes);
            if (open === 'from') {
                // The end moves with the start, the activity keeps its length
                until += minutes - from;
                from = minutes;
            } else {
                until = minutes > from ? minutes : from + STEP;
            }
            open = null;
            save();
            render();
        }
    });

    ui.addEventListener('change', (event) => {
        if (!event.target.classList.contains('js-when-several')) {
            return;
        }
        several = event.target.checked;
        if (several && until < DAY) {
            until += DAY;
        } else if (!several) {
            // Back to one day: the same end time, on the start day or just after midnight
            const time = until % DAY;
            until = time > from ? time : time + DAY;
        }
        open = null;
        save();
        render();
    });

    document.addEventListener('click', (event) => {
        // The path, not contains(): a click inside redraws and detaches its target
        if (open && !event.composedPath().includes(ui)) {
            open = null;
            render();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (open && event.key === 'Escape') {
            const kind = open;
            open = null;
            render();
            ui.querySelector(`[data-open="${kind}"]`)?.focus();
        }
    });

    if (day) {
        save();
    }
    render();
}
