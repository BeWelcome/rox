/*
 * Stays calendar (templates/stays/calendar.html.twig): the filters (all, guests, hosts, maybe) and a picked day narrow the
 * bars and the list; a second click on the day, or Clear, shows the whole month again.
 */
export function initStaysCalendar(page) {
    const title = page.querySelector('.js-stays-list-title');
    const clear = page.querySelector('.js-stays-clear');
    const picked = page.querySelector('.js-stays-picked');
    const none = page.querySelector('.js-stays-none');
    let role = 'all';
    let day = null;

    const update = () => {
        let shown = 0;
        page.querySelectorAll('.js-stays-item').forEach((item) => {
            const roleOk = role === 'all' || (role === 'maybe' ? item.dataset.maybe === '1' : item.dataset.role === role);
            // The list follows the picked day; the bars stay, the day is marked
            const dayOk = !day || !item.dataset.from || (item.dataset.from <= day && item.dataset.to >= day);
            item.hidden = !(roleOk && dayOk);
            if (item.dataset.from && !item.hidden) {
                shown++;
            }
        });
        page.querySelectorAll('.js-stays-day').forEach((cell) => {
            cell.classList.toggle('is-picked', cell.dataset.date === day);
            cell.setAttribute('aria-pressed', cell.dataset.date === day ? 'true' : 'false');
        });
        if (title) {
            // A picked day shows the long date and the count instead of the month title
            title.hidden = !!day;
            if (picked) {
                picked.hidden = !day;
            }
            if (day && picked) {
                const cell = page.querySelector(`.js-stays-day[data-date="${day}"]`);
                const set = (selector, text) => {
                    const el = picked.querySelector(selector);
                    if (el) {
                        el.textContent = text;
                    }
                };
                set('.js-stays-picked-day', cell ? cell.dataset.weekday : day);
                set('.js-stays-picked-count', shown === 1 ? title.dataset.one : title.dataset.other.replace('__COUNT__', shown));
            }
        }
        if (clear) {
            clear.hidden = !day;
        }
        if (none) {
            none.hidden = shown > 0;
        }
        // The "Maybe" title only over maybe stays still shown
        const group = page.querySelector('.js-stays-group');
        if (group) {
            group.hidden = !page.querySelector('.p-stays__row.is-maybe:not([hidden])');
        }
    };

    page.querySelectorAll('.js-stays-filter').forEach((button) => {
        button.addEventListener('click', () => {
            role = button.dataset.role;
            page.querySelectorAll('.js-stays-filter').forEach((other) => {
                other.classList.toggle('is-active', other === button);
                other.setAttribute('aria-pressed', other === button ? 'true' : 'false');
            });
            // The Filter button says the picked one ("Filter" again for All)
            const label = page.querySelector('.js-stays-filter-label');
            const dot = page.querySelector('.js-stays-filter-dot');
            const toggle = page.querySelector('.js-stays-filter-toggle');
            const name = button.querySelector('.js-stays-filter-name');
            if (label) {
                label.textContent = role === 'all' || !name ? label.dataset.default : name.textContent;
            }
            if (dot) {
                dot.className = 'p-stays__dot js-stays-filter-dot' + (button.dataset.dot ? ' p-stays__dot--' + button.dataset.dot : '');
                dot.hidden = !button.dataset.dot;
            }
            if (toggle) {
                toggle.classList.toggle('is-filtered', role !== 'all');
            }
            update();
        });
    });
    page.querySelectorAll('.js-stays-day').forEach((cell) => {
        cell.addEventListener('click', () => {
            day = day === cell.dataset.date ? null : cell.dataset.date;
            update();
        });
    });
    if (clear) {
        clear.addEventListener('click', () => {
            day = null;
            update();
        });
    }
}
