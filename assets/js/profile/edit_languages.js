/*
 * The languages the member speaks (templates/profile/edit/languages.html.twig): one card
 * per language with its level on five steps; a search and a few common languages add a
 * card (from the form's prototype), the cross removes one. The level picked shows on the
 * card's top right. Already added languages are left out of the search and the shortcuts.
 */
const form = document.querySelector('.js-lang-form');

if (form) {
    const list = form.querySelector('.js-lang-list');
    const add = form.querySelector('.js-lang-add');
    const search = add.querySelector('.js-lang-search');
    const results = add.querySelector('.js-lang-results');
    const commons = add.querySelectorAll('.js-lang-common');
    const languages = JSON.parse(add.dataset.languages || '[]');

    const escapeHtml = (value) => String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    // One language at "Native" (the mother tongue) is needed: the hidden box of the hint is
    // ticked once a card is at Native, so the side panel's Save waits for it
    const nativeFlag = form.querySelector('.js-lang-native-flag');
    const checkNative = () => {
        if (nativeFlag) {
            nativeFlag.checked = form.querySelector('.p-lang-level__input[value="mother.tongue"]:checked') !== null;
        }
    };

    // The form changed (Save of the side panel stays pale until then)
    const changed = () => {
        checkNative();
        form.dispatchEvent(new Event('change', { bubbles: true }));
    };

    const added = () => new Set(Array.from(list.querySelectorAll('.js-lang-card')).map((card) => card.dataset.language));

    const showLevel = (card) => {
        const checked = card.querySelector('.p-lang-level__input:checked');
        card.querySelector('.js-lang-card-level').textContent = checked ? checked.dataset.label : '';
        let on = true;
        card.querySelectorAll('.p-lang-level').forEach((level) => {
            level.classList.toggle('is-on', Boolean(checked) && on);
            if (level.contains(checked)) {
                on = false;
            }
        });
    };

    const refreshShortcuts = () => {
        const codes = added();
        commons.forEach((chip) => {
            chip.hidden = codes.has(chip.dataset.code);
        });
    };

    const addLanguage = (code) => {
        const language = languages.find((item) => item.code === code);
        if (!language || added().has(code)) {
            return;
        }
        const index = list.dataset.index;
        const html = list.dataset.prototype
            .replace(/__name__/g, index)
            .replace(/__language__/g, escapeHtml(language.name));
        list.dataset.index = parseInt(index, 10) + 1;
        list.insertAdjacentHTML('beforeend', html);
        const card = list.lastElementChild;
        card.dataset.language = code;
        card.querySelector('input[type="hidden"]').value = code;
        showLevel(card);
        refreshShortcuts();
        changed();
        card.querySelector('.p-lang-level__input').focus();
    };

    // Levels and removal, for the cards there and the ones added later
    list.addEventListener('change', (event) => {
        const card = event.target.closest('.js-lang-card');
        if (card) {
            showLevel(card);
            checkNative();
        }
    });
    list.addEventListener('click', (event) => {
        const remove = event.target.closest('.js-lang-remove');
        if (remove) {
            remove.closest('.js-lang-card').remove();
            refreshShortcuts();
            changed();
        }
    });

    commons.forEach((chip) => chip.addEventListener('click', () => addLanguage(chip.dataset.code)));

    // The search: the first languages whose name starts with, then contains, what is typed
    let matches = [];
    let active = 0;
    const showResults = () => {
        results.innerHTML = matches.map((item, position) => `
            <li role="option" class="p-lang-add__result${position === active ? ' is-active' : ''}" data-code="${escapeHtml(item.code)}" aria-selected="${position === active}">
                ${escapeHtml(item.name)}
            </li>`).join('');
        results.hidden = matches.length === 0;
    };
    const find = () => {
        const text = search.value.trim().toLowerCase();
        const codes = added();
        active = 0;
        if (!text) {
            matches = [];
        } else {
            const free = languages.filter((item) => !codes.has(item.code));
            const starts = free.filter((item) => item.name.toLowerCase().startsWith(text));
            const contains = free.filter((item) => !item.name.toLowerCase().startsWith(text) && item.name.toLowerCase().includes(text));
            matches = starts.concat(contains).slice(0, 8);
        }
        showResults();
    };
    const pick = (code) => {
        addLanguage(code);
        search.value = '';
        matches = [];
        showResults();
    };
    search.addEventListener('input', find);
    search.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown' && matches.length) {
            event.preventDefault();
            active = (active + 1) % matches.length;
            showResults();
        } else if (event.key === 'ArrowUp' && matches.length) {
            event.preventDefault();
            active = (active - 1 + matches.length) % matches.length;
            showResults();
        } else if (event.key === 'Enter') {
            // Never sends the form from the search
            event.preventDefault();
            if (matches[active]) {
                pick(matches[active].code);
            }
        } else if (event.key === 'Escape') {
            matches = [];
            showResults();
        }
    });
    results.addEventListener('mousedown', (event) => {
        const item = event.target.closest('[data-code]');
        if (item) {
            event.preventDefault();
            pick(item.dataset.code);
        }
    });
    search.addEventListener('blur', () => {
        matches = [];
        showResults();
    });

    list.querySelectorAll('.js-lang-card').forEach(showLevel);
    refreshShortcuts();
    checkNative();
}
