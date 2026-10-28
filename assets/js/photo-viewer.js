/*
 * Photo viewer: pictures shown large one at a time, over a dark page, with arrows, a counter
 * and a close button (Escape and a click beside the picture close it too). Used by the search
 * preview and, through lightbox.js, by the links with data-toggle="lightbox" (profile gallery,
 * profile picture, forum pictures).
 *
 * images: [{src, title}], index: the one shown first, labels: {labelClose, labelPrevious, labelNext}.
 */
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

export function openPhotoViewer(images, index, labels = {}) {
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
