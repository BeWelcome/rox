import Lightbox from 'bs5-lightbox';

document.querySelectorAll('[data-toggle="lightbox"]')
    .forEach(
        el => el.addEventListener('click', Lightbox.initialize)
    );
