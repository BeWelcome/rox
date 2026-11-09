/**
 * Member suggestions under a username field: a listbox (.c-member-suggest) with avatars, a loader while searching
 * and keyboard navigation, filled from /member/autocomplete/start. Used by the "Find a member" search of the home
 * page and the volunteer pages that need a member.
 *
 * The list carries its texts: data-loading while searching, data-empty when nothing matches.
 * onPick(username) runs when a member is chosen from the list (click, or Enter on a highlighted one); the field
 * already holds the username then.
 */
export function initMemberSuggest(input, list, onPick = () => {}) {
    const prefix = (list.id || 'member_suggest') + '_option_';
    let timer = null;
    let controller = null;
    let active = -1;

    const open = () => {
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };
    const close = () => {
        list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        active = -1;
    };
    const status = (text, loading) => {
        const item = document.createElement('li');
        item.className = 'c-member-suggest__status';
        if (loading) {
            const spinner = document.createElement('span');
            spinner.className = 'c-member-suggest__spinner';
            spinner.setAttribute('aria-hidden', 'true');
            item.append(spinner);
        }
        item.append(document.createTextNode(text));
        list.replaceChildren(item);
        open();
    };
    const options = () => Array.from(list.querySelectorAll('[role="option"]'));
    const highlight = (index) => {
        const all = options();
        all.forEach((option, i) => option.setAttribute('aria-selected', i === index ? 'true' : 'false'));
        active = index;
        if (all[index]) {
            input.setAttribute('aria-activedescendant', all[index].id);
            all[index].scrollIntoView({ block: 'nearest' });
        }
    };
    const pick = (value) => {
        input.value = value;
        close();
        onPick(value);
    };
    const render = (items, term) => {
        if (items.length === 0) {
            status(list.dataset.empty, false);
            return;
        }
        const lower = term.toLowerCase();
        list.replaceChildren(...items.map((item, i) => {
            const name = String(item.value);
            const option = document.createElement('li');
            option.id = prefix + i;
            option.className = 'c-member-suggest__item';
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', 'false');
            option.dataset.value = name;

            const avatar = document.createElement('img');
            avatar.src = '/members/avatar/' + encodeURIComponent(name) + '/48';
            avatar.alt = '';
            avatar.width = 32;
            avatar.height = 32;
            avatar.loading = 'lazy';

            const label = document.createElement('span');
            label.className = 'c-member-suggest__name';
            const at = name.toLowerCase().indexOf(lower);
            if (at >= 0 && lower !== '') {
                const strong = document.createElement('strong');
                strong.textContent = name.substr(at, term.length);
                label.append(name.substr(0, at), strong, name.substr(at + term.length));
            } else {
                label.textContent = name;
            }

            option.append(avatar, label);
            return option;
        }));
        open();
        active = -1;
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        if (controller) {
            controller.abort();
        }
        const term = input.value.trim();
        if (term.length < 2) {
            close();
            list.replaceChildren();
            return;
        }
        status(list.dataset.loading, true);
        timer = setTimeout(() => {
            controller = new AbortController();
            fetch('/member/autocomplete/start?term=' + encodeURIComponent(term), { signal: controller.signal })
                .then(response => response.json())
                .then(json => render(json.items || [], term))
                .catch(error => {
                    if (error.name !== 'AbortError') {
                        status(list.dataset.empty, false);
                    }
                });
        }, 250);
    });

    input.addEventListener('keydown', (event) => {
        const all = options();
        if (list.hidden || all.length === 0) {
            return;
        }
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            highlight((active + 1) % all.length);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            highlight(active <= 0 ? all.length - 1 : active - 1);
        } else if (event.key === 'Enter' && active >= 0) {
            event.preventDefault();
            pick(all[active].dataset.value);
        } else if (event.key === 'Escape') {
            close();
        }
    });

    // mousedown keeps the focus in the field, so the list is not closed by blur first
    list.addEventListener('mousedown', (event) => {
        const option = event.target.closest('[role="option"]');
        event.preventDefault();
        if (option) {
            pick(option.dataset.value);
        }
    });
    list.addEventListener('mouseover', (event) => {
        const option = event.target.closest('[role="option"]');
        if (option) {
            highlight(options().indexOf(option));
        }
    });
    input.addEventListener('blur', close);
    input.addEventListener('focus', () => {
        if (list.children.length > 0 && input.value.trim().length >= 2) {
            open();
        }
    });
}
