/*
 * The policies (templates/policies/_layout.html.twig): the "On this page" list scrolls smoothly to a section (a jump
 * with reduced motion) and marks the section being read; on phones it starts closed. The language links keep the
 * section (/terms/en#conduct ↔ /terms/fr#conduct). Without JavaScript the list is plain anchor links.
 */
const toc = document.querySelector('.js-policies-toc');
const PHONE = window.matchMedia('(max-width: 575.98px)');
const ACTIVE_LINE = 140;

if (toc) {
    const links = [...toc.querySelectorAll('a[data-section]')];
    const sections = links.map((link) => document.getElementById(link.dataset.section)).filter(Boolean);
    const reduced = window.matchMedia('(prefers-reduced-motion: reduce)');

    if (PHONE.matches) {
        toc.open = false;
    }
    // on wider screens the list is always open: its summary is a heading
    toc.querySelector('summary').addEventListener('click', (event) => {
        if (!PHONE.matches) {
            event.preventDefault();
        }
    });

    links.forEach((link) => {
        link.addEventListener('click', (event) => {
            const section = document.getElementById(link.dataset.section);
            if (!section) {
                return;
            }
            event.preventDefault();
            section.scrollIntoView({behavior: reduced.matches ? 'auto' : 'smooth', block: 'start'});
            history.pushState(null, '', `#${section.id}`);
            if (PHONE.matches) {
                toc.open = false;
            }
        });
    });

    let ticking = false;
    const spy = () => {
        ticking = false;
        let current = sections[0];
        sections.forEach((section) => {
            if (section.getBoundingClientRect().top <= ACTIVE_LINE) {
                current = section;
            }
        });
        links.forEach((link) => {
            const active = current !== undefined && link.dataset.section === current.id;
            link.classList.toggle('is-active', active);
            if (active) {
                link.setAttribute('aria-current', 'location');
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };
    window.addEventListener('scroll', () => {
        if (!ticking) {
            ticking = true;
            window.requestAnimationFrame(spy);
        }
    }, {passive: true});
    spy();
}

document.querySelectorAll('.js-policies-version').forEach((link) => {
    link.addEventListener('click', () => {
        if (window.location.hash !== '') {
            link.href = link.href.split('#')[0] + window.location.hash;
        }
    });
});
