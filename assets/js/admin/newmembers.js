/*
 * The new members (templates/admin/newmembers/list.html.twig): an added country or language filters at once, members
 * are selected for the standard greeting (the dialog lists them and posts to the local or the global greeting), a
 * single greeting opens the message in a new tab and shows as sent here, and a status's Save is enabled once the
 * status changed.
 */
const LIMIT = 50;

const escapeHtml = (value) => String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

/*
 * "Add a country" and "Add a language": the select stays in the form (and works without JavaScript), a search field
 * with a list in the site's look takes its place, like the languages of the profile editor. The list shows all
 * choices on focus (they are few); typing keeps those that start with the text, then those that contain it. A pick
 * sets the select and sends the form.
 */
const searchSelect = (select) => {
    const form = select.form;
    const choices = Array.from(select.options)
        .filter((option) => option.value !== '')
        .map((option) => ({ value: option.value, label: option.textContent.trim() }));
    const placeholder = select.options[0] ? select.options[0].textContent.trim() : '';
    const listId = `${select.name}-${Math.random().toString(36).slice(2)}-list`;

    form.classList.add('is-enhanced');
    select.hidden = true;
    select.tabIndex = -1;
    form.insertAdjacentHTML('beforeend', `
        <div class="c-newmembers__search">
            <i class="fas fa-plus" aria-hidden="true"></i>
            <input type="text" class="c-newmembers__search-input" placeholder="${escapeHtml(placeholder)}"
                   aria-label="${escapeHtml(select.getAttribute('aria-label') || placeholder)}" autocomplete="off"
                   role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="${listId}">
        </div>
        <ul class="c-newmembers__results" id="${listId}" role="listbox" hidden></ul>`);
    const input = form.querySelector('.c-newmembers__search-input');
    const results = form.querySelector('.c-newmembers__results');

    let matches = [];
    let active = 0;
    const show = () => {
        results.innerHTML = matches.map((item, position) => `
            <li role="option" class="c-newmembers__result${position === active ? ' is-active' : ''}" data-value="${escapeHtml(item.value)}" aria-selected="${position === active}">
                ${escapeHtml(item.label)}
            </li>`).join('');
        results.hidden = matches.length === 0;
        input.setAttribute('aria-expanded', String(!results.hidden));
        const current = results.querySelector('.is-active');
        if (current) {
            current.scrollIntoView({ block: 'nearest' });
        }
    };
    const find = () => {
        const text = input.value.trim().toLowerCase();
        const starts = choices.filter((item) => item.label.toLowerCase().startsWith(text));
        const contains = choices.filter((item) => !item.label.toLowerCase().startsWith(text) && item.label.toLowerCase().includes(text));
        matches = starts.concat(contains);
        active = 0;
        show();
    };
    const close = () => {
        matches = [];
        show();
    };
    const pick = (value) => {
        select.value = value;
        input.value = choices.find((item) => item.value === value)?.label ?? input.value;
        close();
        form.requestSubmit();
    };

    input.addEventListener('focus', find);
    input.addEventListener('input', find);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            if (results.hidden) {
                find();
            } else if (matches.length) {
                active = (active + 1) % matches.length;
                show();
            }
        } else if (event.key === 'ArrowUp' && matches.length) {
            event.preventDefault();
            active = (active - 1 + matches.length) % matches.length;
            show();
        } else if (event.key === 'Enter') {
            // only a choice sends the form
            event.preventDefault();
            if (matches[active]) {
                pick(matches[active].value);
            }
        } else if (event.key === 'Escape') {
            close();
        }
    });
    results.addEventListener('mousedown', (event) => {
        const item = event.target.closest('[data-value]');
        if (item) {
            event.preventDefault();
            pick(item.dataset.value);
        }
    });
    input.addEventListener('blur', close);
};

document.querySelectorAll('.js-newmembers-add').forEach(searchSelect);

document.querySelectorAll('.js-newmembers-greeting').forEach((form) => {
    form.addEventListener('submit', () => {
        // after the browser took the form to the new tab
        setTimeout(() => {
            const sent = document.createElement('span');
            sent.className = 'c-newmembers__sent';
            sent.innerHTML = '<i class="fas fa-check" aria-hidden="true"></i>';
            sent.append(form.dataset.sent);
            form.replaceWith(sent);
        });
    });
});

document.querySelectorAll('.js-newmembers-status').forEach((form) => {
    const select = form.querySelector('select');
    const save = form.querySelector('button');
    const initial = select.value;
    const update = () => {
        const changed = select.value !== initial;
        save.disabled = !changed;
        save.classList.toggle('is-changed', changed);
    };
    select.addEventListener('change', update);
    update();
});

const picks = [...document.querySelectorAll('.js-newmembers-pick')];
const all = document.querySelector('.js-newmembers-all');
const allLabel = document.querySelector('.js-newmembers-all-label');
const open = document.querySelector('.js-newmembers-open');
const dialog = document.querySelector('.js-newmembers-dialog');

const selected = () => picks.filter((pick) => pick.checked);

const updateSelection = () => {
    const count = selected().length;
    if (all) {
        all.checked = count > 0 && count === picks.length;
        all.indeterminate = count > 0 && count < picks.length;
        allLabel.textContent = count === 0 ? allLabel.dataset.none : allLabel.dataset.some.replace('%count%', count);
    }
    if (open) {
        open.disabled = count === 0;
        open.querySelector('span').textContent = count === 0 ? open.dataset.none : open.dataset.some.replace('%count%', count);
    }
};

picks.forEach((pick) => {
    pick.addEventListener('change', () => {
        if (pick.checked && selected().length > LIMIT) {
            pick.checked = false;
        }
        updateSelection();
    });
});

all?.addEventListener('change', () => {
    picks.forEach((pick, index) => {
        pick.checked = all.checked && index < LIMIT;
    });
    updateSelection();
});

if (dialog) {
    const form = dialog.querySelector('form');
    const title = dialog.querySelector('.c-newmembers-dialog__title');
    const chips = dialog.querySelector('.js-newmembers-chips');
    const submit = dialog.querySelector('.js-newmembers-submit');

    open.addEventListener('click', () => {
        const members = selected();
        const counted = (element) => (members.length === 1 ? element.dataset.one : element.dataset.label.replace('%count%', members.length));
        title.textContent = counted(title);
        submit.textContent = counted(submit);
        chips.replaceChildren(...members.map((pick) => {
            const chip = document.createElement('span');
            chip.className = 'c-newmembers-dialog__chip';
            const avatar = pick.closest('.js-new-member').querySelector('.c-newmembers__avatar');
            const img = document.createElement('img');
            img.src = avatar.src;
            img.alt = '';
            img.width = 20;
            img.height = 20;
            chip.append(img, pick.value);

            return chip;
        }));
        dialog.showModal();
    });

    dialog.querySelectorAll('.js-newmembers-close').forEach((button) => {
        button.addEventListener('click', () => dialog.close());
    });
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) {
            dialog.close();
        }
    });

    dialog.querySelectorAll('.js-newmembers-kind').forEach((radio) => {
        radio.addEventListener('change', () => {
            form.action = form.dataset[radio.value];
            dialog.querySelectorAll('.js-newmembers-kind').forEach((other) => {
                other.closest('label').classList.toggle('is-active', other.checked);
            });
        });
    });
}

updateSelection();
