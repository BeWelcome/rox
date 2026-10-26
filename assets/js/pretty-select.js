/*
 * A native <select> shown as a button and a small menu in the site's look (the radius
 * and the sort of the search page). The select stays in the form, hidden: a pick sets
 * its value and fires "change", so the listeners of the page work as before.
 */
let openMenu = null;

function closeOpen() {
    if (openMenu) {
        openMenu();
        openMenu = null;
    }
}

document.addEventListener('click', (event) => {
    if (openMenu && !event.target.closest('.p-select.is-open')) {
        closeOpen();
    }
});

export function prettySelect(select) {
    if (!select || select.dataset.pretty) {
        return;
    }
    select.dataset.pretty = '1';

    const wrap = document.createElement('span');
    wrap.className = 'p-select';
    select.parentNode.insertBefore(wrap, select);
    wrap.appendChild(select);
    select.classList.add('p-select__native');
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');

    const button = document.createElement('button');
    button.type = 'button';
    // Same classes as the select: same size and look
    button.className = Array.from(select.classList).filter((name) => name !== 'p-select__native' && !name.startsWith('js-')).join(' ') + ' p-select__button';
    button.setAttribute('aria-haspopup', 'listbox');
    button.setAttribute('aria-expanded', 'false');
    const label = document.querySelector('label[for="' + select.id + '"]');
    if (label) {
        button.id = select.id + '_button';
        label.htmlFor = button.id;
        button.setAttribute('aria-label', label.textContent.trim());
    }
    const text = document.createElement('span');
    text.className = 'p-select__text';
    button.appendChild(text);
    wrap.appendChild(button);

    const menu = document.createElement('ul');
    menu.className = 'p-select__menu';
    menu.setAttribute('role', 'listbox');
    menu.hidden = true;
    wrap.appendChild(menu);

    let active = 0;
    const items = () => Array.from(menu.children);

    const paint = () => {
        const option = select.options[select.selectedIndex];
        text.textContent = option ? option.text.trim() : '';
        items().forEach((item, index) => {
            const picked = index === select.selectedIndex;
            item.classList.toggle('is-picked', picked);
            item.classList.toggle('is-active', index === active);
            item.setAttribute('aria-selected', picked ? 'true' : 'false');
        });
    };

    Array.from(select.options).forEach((option, index) => {
        const item = document.createElement('li');
        item.className = 'p-select__option';
        item.setAttribute('role', 'option');
        item.innerHTML = '<span></span><i class="fas fa-check" aria-hidden="true"></i>';
        item.firstChild.textContent = option.text.trim();
        item.addEventListener('click', () => pick(index));
        item.addEventListener('mousemove', () => {
            if (active !== index) {
                active = index;
                paint();
            }
        });
        menu.appendChild(item);
    });

    const close = () => {
        menu.hidden = true;
        wrap.classList.remove('is-open');
        button.setAttribute('aria-expanded', 'false');
    };
    const open = () => {
        closeOpen();
        active = Math.max(0, select.selectedIndex);
        paint();
        menu.hidden = false;
        wrap.classList.add('is-open');
        button.setAttribute('aria-expanded', 'true');
        // Opens upwards when there is no room below
        const room = window.innerHeight - button.getBoundingClientRect().bottom;
        wrap.classList.toggle('opens-up', room < menu.offsetHeight + 16);
        const picked = items()[active];
        if (picked) {
            picked.scrollIntoView({ block: 'nearest' });
        }
        openMenu = close;
    };
    const pick = (index) => {
        const changed = index !== select.selectedIndex;
        select.selectedIndex = index;
        closeOpen();
        paint();
        button.focus();
        if (changed) {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    };

    button.addEventListener('click', () => (menu.hidden ? open() : closeOpen()));
    button.addEventListener('keydown', (event) => {
        const count = select.options.length;
        if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            if (menu.hidden) {
                open();
                return;
            }
            active = (active + (event.key === 'ArrowDown' ? 1 : -1) + count) % count;
            paint();
            items()[active].scrollIntoView({ block: 'nearest' });
        } else if ((event.key === 'Enter' || event.key === ' ') && !menu.hidden) {
            event.preventDefault();
            pick(active);
        } else if (event.key === 'Escape' && !menu.hidden) {
            event.preventDefault();
            event.stopPropagation();
            closeOpen();
        } else if (event.key === 'Tab') {
            closeOpen();
        }
    });
    // Changed by the page itself
    select.addEventListener('change', paint);

    paint();
}
