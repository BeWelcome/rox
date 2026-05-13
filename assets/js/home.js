import 'jquery';
import 'popper.js';

import 'bootstrap';
import '../scss/home.scss';
import 'cookieconsent/src/cookieconsent.js';
import 'cookieconsent/src/styles/animation.css';
import 'cookieconsent/src/styles/base.css';
import 'cookieconsent/src/styles/layout.css';
import 'cookieconsent/src/styles/media.css';
import 'cookieconsent/src/styles/themes/classic.css';
import 'cookieconsent/src/styles/themes/edgeless.css';
import '../scss/cookie-consent.scss';
import 'select2/dist/js/select2.full.js';
import '@fortawesome/fontawesome-free/js/all.js';
import './collapsemenu.js';
import './password/showhide.js';

$(".select2").select2({
    theme: 'bootstrap4',
    width: 'auto',
    dropdownAutoWidth: true,
});

// ── Home login bottom sheet (mobile only) ──────────────────────────
(function () {
    const aside = document.querySelector('.js-home-login-aside');
    const backdrop = document.querySelector('.js-home-login-backdrop');
    const triggers = document.querySelectorAll('.js-home-login-open');
    const root = document.documentElement;
    let lockedScrollY = 0;
    if (!aside || !backdrop) return;

    function setExpandedState(isExpanded) {
        triggers.forEach(function (btn) {
            btn.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
        });
    }

    function lockBodyScroll() {
        lockedScrollY = window.scrollY || window.pageYOffset || 0;
        document.body.classList.add('home-sheet-open');
        document.body.style.position = 'fixed';
        document.body.style.top = `-${lockedScrollY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        root.style.overflow = 'hidden';
    }

    function unlockBodyScroll() {
        document.body.classList.remove('home-sheet-open');
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        root.style.overflow = '';
        window.scrollTo(0, lockedScrollY);
    }

    function openSheet() {
        if (aside.classList.contains('is-open')) return;
        aside.classList.add('is-open');
        setExpandedState(true);
        // Trigger reflow so CSS transition plays on backdrop
        backdrop.style.display = 'block';
        requestAnimationFrame(function () {
            backdrop.classList.add('is-visible');
        });
        lockBodyScroll();
        const closeBtn = aside.querySelector('.js-home-login-close');
        if (closeBtn) closeBtn.focus();
    }

    function closeSheet() {
        if (!aside.classList.contains('is-open')) return;
        aside.classList.remove('is-open');
        backdrop.classList.remove('is-visible');
        unlockBodyScroll();
        setExpandedState(false);
        // Hide backdrop after transition
        backdrop.addEventListener('transitionend', function onEnd() {
            backdrop.style.display = '';
            backdrop.removeEventListener('transitionend', onEnd);
        }, {once: true});
    }

    triggers.forEach(function (btn) {
        btn.addEventListener('click', openSheet);
    });

    document.querySelectorAll('.js-home-login-close').forEach(function (btn) {
        btn.addEventListener('click', closeSheet);
    });

    backdrop.addEventListener('click', closeSheet);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && aside.classList.contains('is-open')) {
            closeSheet();
        }
    });
}());
