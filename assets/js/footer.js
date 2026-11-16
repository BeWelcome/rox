// Phones: the footer's link groups fold; the heading button of each opens and closes it.
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('.js-footer-toggle');
    if (!toggle) {
        return;
    }
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.closest('.js-footer-group').classList.toggle('is-open', open);
});
