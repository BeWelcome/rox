import {createContactPanel, onContactLinkClick} from '../contact/panel';
import {openPhotoViewer} from '../photo-viewer';

/* ---------------------------------------------------------------------------
 * Member preview: a click on a result opens a short profile over the results
 * (drawer on large screens, sheet on phones) instead of leaving the page.
 * The browser's back button closes it. Ctrl/Cmd/middle click still opens
 * the full profile in a new tab.
 * ------------------------------------------------------------------------ */

let preview = null;
let previewBody = null;
let previewBackdrop = null;
let phone = null;
let onHide = () => {};
let onOpen = () => {};
let previewRequest = null;
let previewOpenedHere = false;

function showPreview() {
    preview.classList.add('is-open');
    preview.setAttribute('aria-hidden', 'false');
    previewBackdrop.hidden = false;
    // Phones only: on large screens the results stay usable next to the drawer.
    document.body.classList.toggle('has-search-overlay', phone.matches);
}

function hidePreview() {
    closeContact(true);
    onHide();
    preview.classList.remove('is-open');
    preview.setAttribute('aria-hidden', 'true');
    previewBackdrop.hidden = true;
    document.body.classList.remove('has-search-overlay');
    document.querySelectorAll('.p-search-card.is-previewed').forEach((card) => card.classList.remove('is-previewed'));
}

function initPreviewPhotos(root) {
    const avatar = root.querySelector('.js-preview-avatar');
    if (avatar) {
        avatar.addEventListener('click', () => openPhotoViewer([{src: avatar.dataset.src, title: ''}], 0));
    }
    const gallery = root.querySelector('.js-preview-gallery');
    if (!gallery) {
        return;
    }
    const images = JSON.parse(gallery.dataset.images || '[]');
    gallery.querySelectorAll('.js-preview-gallery-open').forEach((shot) => {
        shot.addEventListener('click', () => openPhotoViewer(images, Number(shot.dataset.index), gallery.dataset));
    });
}

// Message or stay request written in the preview (see contact/panel.js)
let contact = null;

// After sending, close everything at once. Not with history.back(): the form sent in the
// frame added its own entries to the history, going back would only show the form again.
function closeAfterSent() {
    hidePreview();
    if (previewOpenedHere && history.state && history.state.searchPreview) {
        history.replaceState(null, '');
        previewOpenedHere = false;
    }
}

function contactIsOpen() {
    return contact !== null && contact.isOpen();
}

function closeContact(now) {
    if (contact) {
        contact.close(now);
    }
}


function loadPreview(url, card) {
    document.querySelectorAll('.p-search-card.is-previewed').forEach((other) => other.classList.remove('is-previewed'));
    if (card) {
        card.classList.add('is-previewed');
    }
    if (previewRequest) {
        previewRequest.abort();
    }
    previewRequest = new AbortController();
    closeContact(true);
    previewBody.innerHTML = '<div class="p-search-preview__loading" aria-hidden="true"></div>';
    preview.querySelector('.p-search-preview__body').scrollTop = 0;
    showPreview();

    fetch(url, {headers: {'X-Requested-With': 'XMLHttpRequest'}, signal: previewRequest.signal})
        .then((response) => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }
            return response.text();
        })
        .then((html) => {
            previewBody.innerHTML = html;
            initPreviewPhotos(previewBody);
            initPreviewAbout(previewBody);
        })
        .catch((error) => {
            if (error.name !== 'AbortError') {
                previewBody.innerHTML = '<p class="p-search-preview__error"></p>';
                previewBody.firstChild.textContent = preview.dataset.error;
            }
        });
}

// "About me" is cut after a few lines: "Read more" shows it whole, only when it is cut
function initPreviewAbout(root) {
    const about = root.querySelector('.js-preview-about');
    const more = root.querySelector('.js-preview-about-more');
    if (!about || !more) {
        return;
    }
    // The drawer may still be sliding in: measure once it is laid out
    requestAnimationFrame(() => {
        const cut = about.scrollHeight > about.clientHeight + 1;
        more.hidden = !cut;
        about.classList.toggle('is-cut', cut);
    });
    more.addEventListener('click', () => {
        about.classList.add('is-open');
        about.classList.remove('is-cut');
        more.hidden = true;
    });
}

// Opens a member's preview, with an entry in the history (the back button closes it)
function openPreviewUrl(url, card) {
    if (!preview) {
        window.location.href = url.replace(/\/search\/members\/([^/]+)\/preview$/, '/members/$1');
        return;
    }
    const state = {searchPreview: url};
    if (history.state && history.state.searchPreview) {
        history.replaceState(state, '');
    } else {
        history.pushState(state, '');
        previewOpenedHere = true;
    }
    loadPreview(url, card);
}

// Closing goes back in the history when the preview added an entry to it.
function closePreview() {
    if (!preview.classList.contains('is-open')) {
        return;
    }
    if (previewOpenedHere && history.state && history.state.searchPreview) {
        history.back();
    } else {
        hidePreview();
    }
}

// The preview alone in the side panel (search/preview_panel.html.twig): Read more and the pictures
export function initPreviewContent(root) {
    initPreviewPhotos(root);
    initPreviewAbout(root);
}

/**
 * Starts the member preview of the page (the .js-search-preview drawer). Links with
 * .js-search-preview-open and a data-preview URL open it. onOpen(username) runs when a
 * card of the list opens it, onHide() when it closes (the search map uses both).
 */
export function initMemberPreview(options = {}) {
    preview = document.querySelector('.js-search-preview');
    if (!preview) {
        return {openPreviewUrl};
    }
    previewBody = preview.querySelector('.js-search-preview-body');
    previewBackdrop = document.querySelector('.p-search-preview__backdrop');
    phone = options.phone || window.matchMedia('(max-width: 767.98px)');
    onHide = options.onHide || onHide;
    onOpen = options.onOpen || onOpen;

    const contactPanel = preview.querySelector('.js-search-contact');
    if (contactPanel) {
        contact = createContactPanel(contactPanel, {
            from: options.from || 'search',
            onBack: (source) => (source === 'done' ? closeAfterSent() : contact.close(false)),
        });
        onContactLinkClick('.js-search-contact-open', (link) => contact.open(link));
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('.js-search-preview-open');
        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        event.preventDefault();
        const card = link.closest('.p-search-card');
        openPreviewUrl(link.dataset.preview, card);
        // From the list: the map (on the search page) shows that member
        const username = card ? decodeURIComponent((link.dataset.preview.match(/\/search\/members\/([^/]+)\/preview/) || [])[1] || '') : '';
        if (username) {
            onOpen(username);
        }
    });

    window.addEventListener('popstate', (event) => {
        if (event.state && event.state.searchPreview) {
            loadPreview(event.state.searchPreview, null);
        } else {
            hidePreview();
        }
    });

    document.querySelectorAll('.js-search-preview-close').forEach((button) => button.addEventListener('click', closePreview));
    // A click on the shaded page closes the preview; on another member (a card of the list
    // or a marker of the map), that member's preview is shown instead.
    previewBackdrop.addEventListener('click', (event) => {
        previewBackdrop.hidden = true;
        const under = document.elementFromPoint(event.clientX, event.clientY);
        previewBackdrop.hidden = false;
        const other = under ? under.closest('.js-search-preview-open, .p-search-map-member') : null;
        if (other) {
            other.click();
        } else {
            closePreview();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            if (contactIsOpen()) {
                closeContact(false);
            } else {
                closePreview();
            }
        }
    });

    // Phones: drag the sheet down by its top bar to close it.
    const bar = preview.querySelector('.p-search-preview__bar');
    let startY = null;
    bar.addEventListener('touchstart', (event) => {
        startY = event.touches[0].clientY;
    }, {passive: true});
    bar.addEventListener('touchmove', (event) => {
        if (startY === null) {
            return;
        }
        const offset = Math.max(0, event.touches[0].clientY - startY);
        preview.style.transform = offset ? 'translateY(' + offset + 'px)' : '';
        preview.style.transition = 'none';
    }, {passive: true});
    bar.addEventListener('touchend', (event) => {
        if (startY === null) {
            return;
        }
        const offset = event.changedTouches[0].clientY - startY;
        startY = null;
        preview.style.transform = '';
        preview.style.transition = '';
        if (offset > 80) {
            closePreview();
        }
    });

    // Reloading the page on a preview entry opens it again.
    if (history.state && history.state.searchPreview) {
        loadPreview(history.state.searchPreview, null);
    }

    return {openPreviewUrl};
}
