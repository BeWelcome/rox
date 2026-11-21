/*
 * The search above the conversation list (conversations/_inbox.macros.twig): the list found comes in place of the
 * old one without reloading the page, with a loader meanwhile, and the address follows (back and forward work).
 * The open conversation stays. With a modifier key, or when anything goes wrong, the page loads as usual.
 */
let controller = null;

function searchForm(target) {
    return target.closest('form.c-inbox-search, form#c-inbox-search');
}

// Grey rows in place of the conversations while the new ones load
function skeleton() {
    const rows = document.createElement('div');
    rows.className = 'c-inbox-skeleton';
    rows.setAttribute('aria-hidden', 'true');
    for (let i = 0; i < 6; i++) {
        rows.insertAdjacentHTML('beforeend', '<div class="c-inbox-skeleton__row"><span class="c-inbox-skeleton__avatar"></span>'
            + '<span class="c-inbox-skeleton__lines"><span></span><span></span><span></span></span></div>');
    }

    return rows;
}

function setLoading(loading) {
    const list = document.querySelector('.c-inbox__list');
    if (list) {
        list.classList.toggle('is-searching', loading);
        const items = list.querySelector('.c-inbox-list__items, .c-inbox-list__empty');
        if (loading && items && !list.querySelector('.c-inbox-skeleton')) {
            items.before(skeleton());
        }
        if (!loading) {
            list.querySelectorAll('.c-inbox-skeleton').forEach((rows) => rows.remove());
        }
    }
    const button = document.querySelector('.c-inbox-search__go');
    if (button) {
        button.disabled = loading;
    }
}

async function loadList(url, history, onSwap) {
    const list = document.querySelector('.c-inbox__list');
    if (!list) {
        window.location.assign(url);
        return;
    }
    if (controller) {
        controller.abort();
    }
    controller = new AbortController();
    setLoading(true);

    try {
        const response = await fetch(url, { credentials: 'same-origin', signal: controller.signal });
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const newList = page.querySelector('.c-inbox__list');
        if (!response.ok || !newList) {
            window.location.assign(response.url || url);
            return;
        }
        // The field stays as it is (focus, what was typed meanwhile); what's around it comes from the new list
        const oldSearch = list.querySelector('.c-inbox-search');
        const field = oldSearch && oldSearch.querySelector('.c-inbox-search__field');
        const fresh = document.importNode(newList, true);
        const newSearch = fresh.querySelector('.c-inbox-search');
        const newField = newSearch && newSearch.querySelector('.c-inbox-search__field');
        if (field && newField && newField.parentNode === newSearch) {
            Array.from(oldSearch.childNodes).forEach((node) => { if (node !== field) node.remove(); });
            let before = true;
            Array.from(newSearch.childNodes).forEach((node) => {
                if (node === newField) {
                    before = false;
                } else if (before) {
                    field.before(node);
                } else {
                    oldSearch.append(node);
                }
            });
            newSearch.replaceWith(oldSearch);
        }
        list.replaceChildren(...Array.from(fresh.childNodes));
        if (history === 'push') {
            window.history.pushState({ inbox: true }, '', response.url);
        } else if (history === 'replace') {
            window.history.replaceState({ inbox: true }, '', response.url);
        }
        onSwap();
    } catch (error) {
        if (error.name !== 'AbortError') {
            window.location.assign(url);
        }
        return;
    }
    controller = null;
    setLoading(false);
}

function formUrl(form) {
    const url = new URL(form.action, window.location.href);
    url.search = new URLSearchParams(new FormData(form)).toString();

    return url.href;
}

export function initSearch(onSwap) {
    if (!document.querySelector('.c-inbox__list .c-inbox-search') || !window.fetch || !window.DOMParser || !window.history.pushState) {
        return;
    }

    document.addEventListener('submit', (event) => {
        const form = searchForm(event.target);
        if (!form || event.defaultPrevented) {
            return;
        }
        event.preventDefault();
        clearTimeout(typing);
        loadList(formUrl(form), 'push', onSwap);
    });

    // While typing: after a short pause, from two letters on (or when the field is emptied)
    let typing = null;
    let lastWords = null;
    document.addEventListener('input', (event) => {
        const field = event.target.closest('.c-inbox__list .c-inbox-search__field');
        if (!field || !field.form) {
            return;
        }
        if (lastWords === null) {
            lastWords = new URL(window.location.href).searchParams.get('q') || '';
        }
        clearTimeout(typing);
        const wanted = () => {
            const words = field.value.trim();

            return words !== lastWords.trim() && (words.length === 0 || words.length >= 2);
        };
        // The skeleton right away, the search after a short pause
        if (wanted()) {
            setLoading(true);
        }
        typing = setTimeout(() => {
            if (!wanted()) {
                if (!controller) {
                    setLoading(false);
                }
                return;
            }
            lastWords = field.value.trim();
            loadList(formUrl(field.form), 'replace', onSwap);
        }, 400);
    });

    // The cross that empties the field
    document.addEventListener('click', (event) => {
        const link = event.target.closest('.c-inbox-search__clear');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        event.preventDefault();
        clearTimeout(typing);
        lastWords = '';
        const field = document.querySelector('.c-inbox__list .c-inbox-search__field');
        if (field) {
            field.value = '';
            field.focus();
        }
        loadList(link.href, 'push', onSwap);
    });

    // Back and forward: the list of that address, when its search isn't the one shown
    window.addEventListener('popstate', () => {
        const field = document.querySelector('.c-inbox-search__field');
        const words = new URL(window.location.href).searchParams.get('q') || '';
        if (field && field.value.trim() !== words.trim()) {
            loadList(window.location.href, null, onSwap);
        }
    });
}
