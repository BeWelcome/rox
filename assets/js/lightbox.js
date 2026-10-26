import '../scss/_lightbox.scss';
import Lightbox from 'bs5-lightbox';

// bs5-lightbox registers `[data-toggle="lightbox"]` when this module loads.
// Only bind `[data-bs-toggle="lightbox"]` here — binding both would run two handlers on the same link.
document.querySelectorAll('[data-bs-toggle="lightbox"]').forEach((el) => {
  el.addEventListener('click', Lightbox.initialize);
});
