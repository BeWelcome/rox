import {createContactPanel, onContactLinkClick} from '../contact/panel';

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

// The member's pictures in the preview: a mosaic, and a click shows them large one at a time,
// with arrows, a counter and a close button (Escape closes it too).
let viewer = null;

function buildViewer() {
    viewer = document.createElement('div');
    viewer.className = 'p-photo-viewer';
    viewer.hidden = true;
    viewer.setAttribute('role', 'dialog');
    viewer.setAttribute('aria-modal', 'true');
    viewer.innerHTML = `
        <button type="button" class="p-photo-viewer__close js-viewer-close" aria-label="Close"><i class="fas fa-times" aria-hidden="true"></i></button>
        <span class="p-photo-viewer__counter js-viewer-counter"></span>
        <button type="button" class="p-photo-viewer__nav p-photo-viewer__nav--prev js-viewer-prev" aria-label="Previous"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
        <figure class="p-photo-viewer__figure">
            <img class="p-photo-viewer__img js-viewer-img" alt="">
            <figcaption class="p-photo-viewer__caption js-viewer-caption"></figcaption>
        </figure>
        <button type="button" class="p-photo-viewer__nav p-photo-viewer__nav--next js-viewer-next" aria-label="Next"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
    `;
    document.body.appendChild(viewer);

    viewer.querySelector('.js-viewer-close').addEventListener('click', closeViewer);
    viewer.querySelector('.js-viewer-prev').addEventListener('click', () => showImage(viewer.index - 1));
    viewer.querySelector('.js-viewer-next').addEventListener('click', () => showImage(viewer.index + 1));
    // A click beside the picture closes it.
    viewer.addEventListener('click', (event) => {
        if (event.target === viewer || event.target.classList.contains('p-photo-viewer__figure')) {
            closeViewer();
        }
    });
    // Before the page's own Escape (which closes the preview): only the viewer closes.
    window.addEventListener('keydown', (event) => {
        if (viewer.hidden) {
            return;
        }
        if (event.key === 'Escape') {
            event.stopPropagation();
            closeViewer();
        } else if (event.key === 'ArrowLeft') {
            showImage(viewer.index - 1);
        } else if (event.key === 'ArrowRight') {
            showImage(viewer.index + 1);
        }
    }, true);
}

function showImage(index) {
    const images = viewer.images;
    if (index < 0 || index >= images.length) {
        return;
    }
    viewer.index = index;
    const img = viewer.querySelector('.js-viewer-img');
    img.src = images[index].src;
    img.alt = images[index].title || '';
    viewer.querySelector('.js-viewer-caption').textContent = images[index].title || '';
    viewer.querySelector('.js-viewer-counter').textContent = images.length > 1 ? (index + 1) + ' / ' + images.length : '';
    viewer.querySelector('.js-viewer-prev').hidden = index <= 0;
    viewer.querySelector('.js-viewer-next').hidden = index >= images.length - 1;
}

function openViewer(images, index, labels) {
    if (!viewer) {
        buildViewer();
    }
    viewer.querySelector('.js-viewer-close').setAttribute('aria-label', labels.labelClose || 'Close');
    viewer.querySelector('.js-viewer-prev').setAttribute('aria-label', labels.labelPrevious || 'Previous');
    viewer.querySelector('.js-viewer-next').setAttribute('aria-label', labels.labelNext || 'Next');
    viewer.images = images;
    viewer.opener = document.activeElement;
    viewer.hidden = false;
    showImage(index);
    viewer.querySelector('.js-viewer-close').focus({preventScroll: true});
}

function closeViewer() {
    viewer.hidden = true;
    viewer.querySelector('.js-viewer-img').removeAttribute('src');
    if (viewer.opener) {
        viewer.opener.focus({preventScroll: true});
    }
}

function initPreviewPhotos(root) {
    const gallery = root.querySelector('.js-preview-gallery');
    if (!gallery) {
        return;
    }
    const images = JSON.parse(gallery.dataset.images || '[]');
    gallery.querySelectorAll('.js-preview-gallery-open').forEach((shot) => {
        shot.addEventListener('click', () => openViewer(images, Number(shot.dataset.index), gallery.dataset));
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
