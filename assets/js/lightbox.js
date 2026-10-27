import {openPhotoViewer} from './photo-viewer';

// Links with data-toggle="lightbox" (or data-bs-toggle): the picture they point to opens in the
// site's photo viewer, with the other links of the same data-gallery to go through. A plain left
// click only: Cmd/Ctrl-click still opens the picture in a new tab.
document.addEventListener('click', (event) => {
    const link = event.target.closest('a[data-toggle="lightbox"], a[data-bs-toggle="lightbox"]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }
    event.preventDefault();
    const group = link.dataset.gallery;
    const links = group
        ? Array.from(document.querySelectorAll('a[data-toggle="lightbox"], a[data-bs-toggle="lightbox"]'))
            .filter((other) => other.dataset.gallery === group)
        : [link];
    const images = links.map((other) => ({src: other.href, title: other.dataset.caption || ''}));
    openPhotoViewer(images, Math.max(0, links.indexOf(link)));
});
