import {createContactPanel, onContactLinkClick} from '../contact/panel';

/*
 * The translation tool (templates/admin/translations/tool/): a row of the list opens its text in the side panel,
 * with the previous and the next one of the list (?position=&total=, "bw-translation-step"). Once saved
 * ("bw-translation-saved"), the row shows the new text as translated and the next text opens; after the last one
 * the panel closes. "New text" opens in the panel too.
 */

function rowsOf(list) {
    return list ? Array.from(list.querySelectorAll('.js-translation-row')) : [];
}

function today() {
    return new Intl.DateTimeFormat(document.documentElement.lang || undefined, {dateStyle: 'medium'}).format(new Date());
}

async function rendered(list, code) {
    const response = await fetch(list.dataset.renderUrl, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({code, locale: list.dataset.locale, parameterSets: [{}]}),
        credentials: 'same-origin',
    });
    if (!response.ok) {
        throw new Error(`translation_render: ${response.status}`);
    }

    return (await response.json()).texts[0];
}

/*
 * The row of a saved text: translated, today, with its new text (as text: the list shows no markup).
 */
async function showSaved(list, row) {
    row.className = row.className.replace(/p-tr-row--\S+/, 'p-tr-row--ok');
    const state = row.querySelector('.js-translation-state');
    if (state) {
        state.className = 'p-tr-state p-tr-state--ok js-translation-state';
        state.textContent = list.dataset.labelTranslated;
    }
    const date = row.querySelector('.js-translation-date');
    if (date) {
        date.textContent = list.dataset.labelTranslatedOn.replace('%date%', today());
    }
    try {
        const element = document.createElement('div');
        element.innerHTML = await rendered(list, row.dataset.code);
        const translation = row.querySelector('.p-tr-row__translation');
        const text = document.createElement('p');
        text.className = 'p-tr-row__text js-translation-text';
        text.textContent = element.textContent.trim();
        translation.querySelectorAll('.p-tr-row__none, .js-translation-text').forEach((old) => old.remove());
        translation.append(text);
    } catch (error) {
        // the new text shows after the next reload
    }
    // a text with a change of the English text: the change is followed now
    row.querySelectorAll('.p-tr-row__english del').forEach((removed) => removed.remove());
    row.querySelectorAll('.p-tr-row__english ins').forEach((added) => added.replaceWith(added.textContent));
}

export function initTranslationTool() {
    const drawer = document.querySelector('.js-tool-drawer');
    if (!drawer) {
        return;
    }
    const list = document.querySelector('.js-translation-list');
    const backdrop = document.querySelector('.js-tool-drawer-close');
    const frame = drawer.querySelector('.js-search-contact-frame');
    let current = null;

    const mark = (row) => {
        rowsOf(list).forEach((other) => other.classList.toggle('is-active', other === row));
    };
    const close = () => {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        backdrop.hidden = true;
        panel.close(false);
        current = null;
        mark(null);
    };
    const panel = createContactPanel(drawer.querySelector('.js-search-contact'), {from: 'translation', onBack: close});

    const open = (link) => {
        panel.open(link);
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        backdrop.hidden = false;
    };
    // a row of the list: with its position, for previous and next
    const openRow = (row) => {
        const rows = rowsOf(list);
        const link = row && row.querySelector('.js-tool-open');
        if (!link) {
            close();

            return;
        }
        const url = new URL(link.href, window.location.href);
        url.searchParams.set('position', String(rows.indexOf(row) + 1));
        url.searchParams.set('total', String(rows.length));
        current = row;
        mark(row);
        open({href: url.toString(), dataset: {title: link.dataset.title}, textContent: link.textContent});
    };

    onContactLinkClick('.js-tool-open', (link) => {
        const row = link.closest('.js-translation-row');
        if (row) {
            openRow(row);
        } else {
            current = null;
            open(link);
        }
    });
    // the whole row opens the text
    if (list) {
        list.addEventListener('click', (event) => {
            const row = event.target.closest('.js-translation-row');
            if (!row || event.target.closest('a, button') || window.getSelection().toString() !== '') {
                return;
            }
            openRow(row);
        });
    }
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
            close();
        }
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || event.source !== frame.contentWindow || !event.data) {
            return;
        }
        const rows = rowsOf(list);
        const index = current ? rows.indexOf(current) : -1;
        if (event.data.type === 'bw-translation-step' && index >= 0) {
            const next = rows[index + Number(event.data.step)];
            if (next) {
                openRow(next);
            }
        } else if (event.data.type === 'bw-translation-saved') {
            if (index < 0) {
                // a new text: the list may have it now
                close();
                window.location.reload();

                return;
            }
            showSaved(list, current);
            const next = rows[index + 1];
            if (next) {
                openRow(next);
            } else {
                close();
            }
        }
    });
}

initTranslationTool();
