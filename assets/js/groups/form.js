import './location';

/*
 * The group forms (new group, Edit group): the categories as pills, at most three (the others are greyed then),
 * suggestions from the name and description typed, and the place: anywhere or tied to a place.
 */
const picker = document.querySelector('.js-cat-picker');

if (picker) {
    const max = Number(picker.dataset.max);
    const inputs = [...picker.querySelectorAll('.js-cat-input')];
    const count = picker.querySelector('.js-cat-count');
    const suggest = picker.querySelector('.js-cat-suggest');
    const suggestList = picker.querySelector('.js-cat-suggest-list');
    const form = picker.closest('form');

    const picked = () => inputs.filter(input => input.checked);

    const refresh = () => {
        const full = picked().length >= max;
        inputs.forEach(input => {
            input.disabled = full && !input.checked;
        });
        count.textContent = picker.dataset.countText.replace('__count__', String(picked().length));
        suggestList.querySelectorAll('button').forEach(button => {
            const input = inputs.find(item => item.value === button.dataset.id);
            button.hidden = !input || input.checked;
            button.disabled = full;
        });
        suggest.hidden = [...suggestList.querySelectorAll('button')].every(button => button.hidden);
    };

    // The name and the description (the editor's text once it is there) a moment after the last change
    const text = () => {
        const name = form.querySelector('input[name$="[name]"]');
        const editable = form.querySelector('.ck-editor__editable');
        const description = editable ? editable.textContent : (form.querySelector('textarea')?.value ?? '');
        return ((name ? name.value : '') + ' ' + description).trim();
    };

    let timer = 0;
    let last = null;
    const ask = () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            const current = text();
            if (current === last) {
                return;
            }
            last = current;
            fetch(picker.dataset.suggestUrl, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({text: current, selected: picked().map(input => Number(input.value))}),
            })
                .then(response => response.ok ? response.json() : [])
                .then(categories => {
                    suggestList.replaceChildren(...categories.map(category => {
                        const button = document.createElement('button');
                        button.type = 'button';
                        button.className = 'c-cat-pill c-cat-pill--suggested';
                        button.dataset.id = String(category.id);
                        const icon = document.createElement('i');
                        icon.className = 'fas fa-plus';
                        icon.setAttribute('aria-hidden', 'true');
                        const label = document.createElement('span');
                        label.textContent = category.name;
                        button.append(icon, label);
                        button.addEventListener('click', () => {
                            const input = inputs.find(item => item.value === button.dataset.id);
                            if (input && !input.disabled) {
                                input.checked = true;
                                refresh();
                            }
                        });
                        return button;
                    }));
                    refresh();
                })
                .catch(() => {});
        }, 400);
    };

    inputs.forEach(input => input.addEventListener('change', refresh));
    form.addEventListener('input', ask);
    new MutationObserver(ask).observe(form, {childList: true, characterData: true, subtree: true});
    refresh();
    ask();
}

const place = document.querySelector('.js-place-picker');
if (place) {
    const fields = place.querySelector('.js-place-fields');
    place.querySelectorAll('.js-place-mode').forEach(mode => mode.addEventListener('change', () => {
        const tied = mode.value === 'tied' && mode.checked;
        fields.hidden = !tied;
        if (!tied) {
            fields.querySelectorAll('input').forEach(input => {
                input.value = '';
            });
        } else {
            fields.querySelector('input[type="text"], input:not([type])')?.focus();
        }
    }));
}
