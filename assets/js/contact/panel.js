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

    function isOpen() {
        return panel.classList.contains('is-open');
    }

    function open(link) {
        const url = new URL(link.href, window.location.href);
        url.searchParams.set('embed', '1');
        url.searchParams.set('from', from);
        panel.classList.remove('is-sent', 'is-ready');
        title.textContent = link.dataset.title || link.textContent.trim();
        frame.title = link.dataset.title || '';
        frame.src = url.toString();
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
                frame.src = 'about:blank';
            }
        };
        if (now) {
            reset();
        } else {
            setTimeout(reset, 250);
        }
    }

    panel.querySelectorAll('.js-search-contact-back').forEach((button) => button.addEventListener('click', () => onBack('arrow')));

    frame.addEventListener('load', () => {
        let location = null;
        try {
            location = frame.contentWindow.location;
        } catch (error) {
            return;
        }
        if (location.href !== 'about:blank' && !location.pathname.startsWith('/new/')) {
            window.location.href = location.href;
        }
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
