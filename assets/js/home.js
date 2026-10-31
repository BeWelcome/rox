import '@popperjs/core';

import * as bootstrap from 'bootstrap'

import 'cookieconsent/src/cookieconsent.js';
import 'cookieconsent/src/styles/animation.css';
import 'cookieconsent/src/styles/base.css';
import 'cookieconsent/src/styles/layout.css';
import 'cookieconsent/src/styles/media.css';
import 'cookieconsent/src/styles/themes/classic.css';
import 'cookieconsent/src/styles/themes/edgeless.css';
import '../scss/cookie-consent.scss';
// import 'select2/dist/js/select2.full.js';
import '@fortawesome/fontawesome-free/js/all.js';
import './collapsemenu.js';
import '../scss/home.scss';

window.bootstrap = bootstrap;

// Front page: the real map under the map search drawing, loaded only there
// Front page hero: each corner of the photo turns through its cards, one corner after the other
const frontpageSlots = document.querySelectorAll('.js-fp-slot');
if (frontpageSlots.length && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    let turn = 0;
    setInterval(function () {
        if (document.hidden) {
            return;
        }
        const slot = frontpageSlots[turn % frontpageSlots.length];
        turn += 1;
        const cards = slot.children;
        const current = slot.querySelector('.is-active');
        const next = cards[(Array.prototype.indexOf.call(cards, current) + 1) % cards.length];
        current.classList.remove('is-active');
        current.classList.add('is-leaving');
        next.classList.add('is-active');
        setTimeout(function () {
            current.classList.remove('is-leaving');
        }, 500);
    }, 2200);
}

// Front page hero: the member count runs up from 0 in two seconds once it is on screen
const frontpageCount = document.querySelector('.js-fp-count');
if (frontpageCount && 'IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const target = parseInt(frontpageCount.dataset.count, 10) || 0;
    const format = new Intl.NumberFormat(document.documentElement.lang || undefined);
    const finalText = frontpageCount.textContent;
    frontpageCount.textContent = format.format(0);
    const observer = new IntersectionObserver(function (entries) {
        if (!entries[0].isIntersecting) {
            return;
        }
        observer.disconnect();
        const start = performance.now();
        const step = function (now) {
            const progress = Math.min((now - start) / 2000, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            frontpageCount.textContent = progress < 1 ? format.format(Math.round(target * eased)) : finalText;
            if (progress < 1) {
                requestAnimationFrame(step);
            }
        };
        requestAnimationFrame(step);
    });
    observer.observe(frontpageCount);
}

const frontpageMap = document.querySelector('.js-fp-map');
if (frontpageMap) {
    import('./home/frontpage-map').then(({ showFrontpageMap }) => showFrontpageMap(frontpageMap));
}

// ── Home login panel (sheet on phones, card on desktop) ──────────────────────────
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

    // On desktop the panel hangs under the sticky top bar, so the page only stops scrolling (a fixed body would
    // move the top bar away); phones need the fixed body for iOS
    const desktop = window.matchMedia('(min-width: 768px)');
    let lockedFixed = false;

    function lockBodyScroll() {
        lockedScrollY = window.scrollY || window.pageYOffset || 0;
        document.body.classList.add('home-sheet-open');
        root.style.overflow = 'hidden';
        lockedFixed = !desktop.matches;
        if (!lockedFixed) return;
        document.body.style.position = 'fixed';
        document.body.style.top = `-${lockedScrollY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
    }

    function unlockBodyScroll() {
        document.body.classList.remove('home-sheet-open');
        root.style.overflow = '';
        if (!lockedFixed) return;
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
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
        const first = desktop.matches ? aside.querySelector('input[name="_username"]') : aside.querySelector('.js-home-login-close');
        if (first) first.focus();
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
        btn.addEventListener('click', function (event) {
            event.preventDefault();
            openSheet();
        });
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
