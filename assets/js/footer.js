// Below 768px the footer links are folded: the button above them opens and closes them.
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('.js-footer-toggle');
    if (!toggle) {
        return;
    }
    const open = toggle.getAttribute('aria-expanded') !== 'true';
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.closest('.stickyfooter__main').classList.toggle('is-open', open);
});
