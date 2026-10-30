/*
 * Message or stay request written in a side panel (search preview, profile page): the
 * form page (?embed=1, without menu and footer) slides in a frame. Once sent, the frame
 * shows a confirmation and posts "bw-contact-sent"; its "back" button posts
 * "bw-contact-close". The form page has its own head: it posts "bw-contact-ready" once
 * loaded and "bw-contact-back" from its back arrow.
 * If the form page sends the member somewhere else instead (not
 * allowed to write, limit reached...), that page opens in the window.
 *
 * panel: the .js-search-contact element. from: "search" or "profile", tells the
 * confirmation which way back to offer. onBack(source): called by the back arrow
 * ("arrow") and by the confirmation's back button ("done").
 */
export function createContactPanel(panel, {from, onBack}) {
    const frame = panel.querySelector('.js-search-contact-frame');
    const title = panel.querySelector('.js-search-contact-title');

    // Without adding entries to the window's history (the back button stays on the page)
    function load(url) {
        try {
            frame.contentWindow.location.replace(url);
        } catch (error) {
            frame.src = url;
        }
    }

    function isOpen() {
        return panel.classList.contains('is-open');
    }

    function open(link) {
        const url = new URL(link.href, window.location.href);
        url.searchParams.set('embed', '1');
        url.searchParams.set('from', from);
        // Loading: a spinner instead of the frame and its head, until the page is loaded
        panel.classList.remove('is-sent', 'is-ready', 'is-loaded');
        title.textContent = link.dataset.title || link.textContent.trim();
        frame.title = link.dataset.title || '';
        load(url.toString());
        panel.hidden = false;
        // Next frame, so that the slide is animated
        requestAnimationFrame(() => requestAnimationFrame(() => panel.classList.add('is-open')));
    }

    function close(now) {
        if (panel.hidden) {
            return;
        }
        panel.classList.remove('is-open');
        const reset = () => {
            if (!isOpen()) {
                panel.hidden = true;
                load('about:blank');
            }
        };
        if (now) {
            reset();
        } else {
            setTimeout(reset, 250);
        }
    }

    // The panel's page sends elsewhere (EmbedFrameListener, "bw-embed-leave"): that page
    // opens in the window. On the edit page a saved section sends back to the profile or
    // to the edit page: the edit page is shown again instead, with what was just saved.
    // The edit page itself redraws in place (profile/edit.js, "bw-edit-saved").
    function leave(href) {
        const url = new URL(href, window.location.href);
        if (from === 'edit') {
            const target = /\/edit(\/|$)/.test(url.pathname) ? url.toString() : window.location.href;
            onBack('done');
            window.dispatchEvent(new CustomEvent('bw-edit-saved', { detail: { url: target } }));
        } else {
            const here = new URL(window.location.href);
            window.location.href = url.toString();
            // Same page, only another #anchor (a forum reply: the thread at the new post):
            // the browser would just scroll, so load it again
            if (url.origin === here.origin && url.pathname === here.pathname && url.search === here.search) {
                window.location.reload();
            }
        }
    }

    panel.querySelectorAll('.js-search-contact-back').forEach((button) => button.addEventListener('click', () => onBack('arrow')));

    frame.addEventListener('load', () => {
        let location = null;
        try {
            location = frame.contentWindow.location;
            // A page the browser refused to show here can't be read: show what it shows
            location.href;
        } catch (error) {
            panel.classList.add('is-loaded');
            return;
        }
        if (location.href === 'about:blank') {
            return;
        }
        // A page made for the panel keeps ?embed=1; any other one opens in the window
        if (!new URLSearchParams(location.search).has('embed')) {
            leave(location.href);
            return;
        }
        panel.classList.add('is-loaded');
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || !event.data || event.source !== frame.contentWindow) {
            return;
        }
        if (event.data.type === 'bw-contact-ready') {
            // The form page has its own head (back arrow, member): hide the panel's one
            panel.classList.add('is-ready');
        } else if (event.data.type === 'bw-contact-back') {
            onBack('arrow');
        } else if (event.data.type === 'bw-contact-sent') {
            panel.classList.add('is-sent');
        } else if (event.data.type === 'bw-contact-close') {
            onBack('done');
        } else if (event.data.type === 'bw-embed-leave' && typeof event.data.url === 'string') {
            leave(event.data.url);
        }
    });

    return {open, close, isOpen};
}

// Links that open the panel: a plain left click only (Cmd/Ctrl-click keeps the full page).
export function onContactLinkClick(selector, handler) {
    document.addEventListener('click', (event) => {
        const link = event.target.closest(selector);
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        event.preventDefault();
        handler(link);
    });
}
