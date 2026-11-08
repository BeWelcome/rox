/*
 * Inbox on a computer (list and conversation side by side): a conversation picked in the
 * list loads in the pane on the right, the list and the rest of the page stay. A loader
 * shows in the pane meanwhile, the address follows (back and forward work), and the
 * conversation's scripts start again on the new pane (onSwap). Phones too: the list gives
 * way to the conversation, and its back arrow brings the list back, without reloading.
 * With a modifier key, or when anything goes wrong, the page loads as usual.
 */
import MicroModal from 'micromodal';

const desktop = window.matchMedia('(min-width: 768px)');
let controller = null;

function showLoader(pane) {
    if (!pane.querySelector('.c-inbox-pane__loader')) {
        const loader = document.createElement('span');
        loader.className = 'c-inbox-pane__loader';
        loader.setAttribute('aria-hidden', 'true');
        pane.append(loader);
    }
    pane.classList.add('is-loading');
}

// The list: the picked conversation marked, read now
function markActive(href) {
    const path = new URL(href, window.location.href).pathname;
    document.querySelectorAll('.c-inbox__list .c-inbox-item').forEach((item) => {
        const link = item.querySelector('.c-inbox-item__link');
        const active = link && new URL(link.href, window.location.href).pathname === path;
        item.classList.toggle('is-active', active);
        if (link) {
            if (active) {
                link.setAttribute('aria-current', 'true');
            } else {
                link.removeAttribute('aria-current');
            }
        }
        if (active) {
            item.classList.remove('is-unread');
        }
    });
}

// Styles the new page needs that this one doesn't have yet (the stay calendar of requests)
function addStylesheets(page) {
    const have = new Set(Array.from(document.querySelectorAll('link[rel="stylesheet"]')).map((link) => link.href));
    page.querySelectorAll('link[rel="stylesheet"]').forEach((link) => {
        const href = new URL(link.getAttribute('href'), window.location.href).href;
        if (!have.has(href)) {
            const copy = document.createElement('link');
            copy.rel = 'stylesheet';
            copy.href = href;
            document.head.append(copy);
        }
    });
}

// What sits after the inbox: the report form of this conversation (replaced) and the
// member's preview drawer (added once, then started)
function swapExtras(page, inbox) {
    const report = page.getElementById('report-modal');
    document.getElementById('report-modal')?.remove();
    if (report) {
        inbox.after(document.importNode(report, true));
    }
    if (!document.querySelector('.js-search-preview') && page.querySelector('.js-search-preview')) {
        const parts = page.querySelectorAll('.js-places-preview, .js-search-preview-backdrop, .js-search-preview');
        let anchor = document.getElementById('report-modal') || inbox;
        parts.forEach((part) => {
            const copy = document.importNode(part, true);
            anchor.after(copy);
            anchor = copy;
        });
        import('../search/preview').then(({initMemberPreview}) => initMemberPreview());
    }
    MicroModal.init({ awaitCloseAnimation: true });
}

async function load(href, push, onSwap) {
    const inbox = document.querySelector('.c-inbox');
    const pane = inbox && inbox.querySelector('.c-inbox__pane');
    if (!pane) {
        window.location.assign(href);
        return;
    }
    if (controller) {
        controller.abort();
    }
    controller = new AbortController();
    markActive(href);
    // Phones show the list or the conversation: the loader goes on the one on screen
    const list = inbox.querySelector('.c-inbox__list');
    const onScreen = !desktop.matches && list && list.offsetParent !== null ? list : pane;
    showLoader(onScreen);
    const clearList = () => {
        if (list) {
            list.classList.remove('is-loading');
            list.querySelectorAll('.c-inbox-pane__loader').forEach((loader) => loader.remove());
            list.querySelectorAll('.c-inbox-item.is-loading').forEach((item) => item.classList.remove('is-loading'));
        }
    };

    try {
        const response = await fetch(href, { credentials: 'same-origin', signal: controller.signal });
        const page = new DOMParser().parseFromString(await response.text(), 'text/html');
        const newInbox = page.querySelector('.c-inbox');
        const newPane = newInbox && newInbox.querySelector('.c-inbox__pane');
        // Not a conversation of the inbox (an error, the login page...): show it as a page
        if (!response.ok || !newPane || new URL(response.url).pathname !== new URL(href, window.location.href).pathname) {
            window.location.assign(response.url || href);
            return;
        }
        addStylesheets(page);
        const fresh = document.importNode(newPane, true);
        fresh.classList.add('is-arriving', 'is-ready');
        document.querySelector('.c-inbox__pane').replaceWith(fresh);
        requestAnimationFrame(() => requestAnimationFrame(() => fresh.classList.remove('is-arriving')));
        inbox.className = newInbox.className;
        clearList();
        if (!desktop.matches) {
            window.scrollTo(0, 0);
        }
        swapExtras(page, inbox);
        document.title = page.title;
        if (push) {
            window.history.pushState({ inbox: true }, '', response.url);
        }
        onSwap();
        // The envelope in the top bar counts again (updateCounters.js)
        window.dispatchEvent(new CustomEvent('bw-conversations-read'));
    } catch (error) {
        if (error.name !== 'AbortError') {
            window.location.assign(href);
        }
    }
}

export function initInboxNav(onSwap) {
    if (!document.querySelector('.c-inbox .c-inbox__pane') || !window.fetch || !window.DOMParser || !window.history.pushState) {
        return;
    }
    // This page as the first entry, so that going back to it comes back here
    window.history.replaceState({ inbox: true }, '', window.location.href);

    document.addEventListener('click', (event) => {
        // A conversation of the list, or (phones) the arrow back from a conversation to the list
        const link = event.target.closest('.c-inbox__list .c-inbox-item__link, .c-inbox__pane .c-inbox-head__back');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        event.preventDefault();
        if (link.getAttribute('aria-current') === 'true' && !document.querySelector('.c-inbox__pane--empty') && desktop.matches) {
            return;
        }
        if (link.closest('.c-inbox-item')) {
            link.closest('.c-inbox-item').classList.add('is-loading');
        }
        load(link.href, true, onSwap);
    });

    // Back to the list from the browser's cache (phones): no loader left over
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            document.querySelectorAll('.c-inbox-pane__loader').forEach((loader) => loader.remove());
            document.querySelectorAll('.c-inbox .is-loading').forEach((element) => element.classList.remove('is-loading'));
        }
    });

    window.addEventListener('popstate', (event) => {
        if (!event.state || !event.state.inbox) {
            return;
        }
        load(window.location.href, false, onSwap);
    });
}
