import {createContactPanel, onContactLinkClick} from './panel';

/*
 * The side panel of templates/conversations/_contact_drawer.html.twig: links with
 * js-contact-open (write a message, request a stay, invite) open their form in it.
 * from: "profile", "home" or "visitors" (or the drawer's data-from), tells the
 * confirmation which way back to offer.
 */
export function initContactDrawer(from) {
    const contactDrawer = document.querySelector('.js-contact-drawer');
    if (!contactDrawer) {
        return;
    }
    from = from || contactDrawer.dataset.from || 'home';
    const backdrop = document.querySelector('.js-contact-drawer-close');
    // The row whose trip is in the panel stays marked (js-contact-row)
    const markRow = (link) => {
        document.querySelectorAll('.js-contact-row.is-active').forEach((row) => row.classList.remove('is-active'));
        const row = link ? link.closest('.js-contact-row') : null;
        if (row) {
            row.classList.add('is-active');
        }
    };
    const closeDrawer = () => {
        contactDrawer.classList.remove('is-open');
        contactDrawer.setAttribute('aria-hidden', 'true');
        backdrop.hidden = true;
        contact.close(false);
        markRow(null);
    };
    const contact = createContactPanel(contactDrawer.querySelector('.js-search-contact'), {
        from,
        onBack: closeDrawer,
    });
    onContactLinkClick('.js-contact-open', (link) => {
        contact.open(link);
        markRow(link);
        contactDrawer.classList.add('is-open');
        contactDrawer.setAttribute('aria-hidden', 'false');
        backdrop.hidden = false;
    });
    // A click on the shaded page closes the panel, except on another row: its trip
    // replaces the one in the panel.
    backdrop.addEventListener('click', (event) => {
        backdrop.hidden = true;
        const under = document.elementFromPoint(event.clientX, event.clientY);
        backdrop.hidden = false;
        const link = under ? under.closest('.js-contact-row .js-contact-open') : null;
        if (link) {
            link.click();
        } else {
            closeDrawer();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && contactDrawer.classList.contains('is-open')) {
            closeDrawer();
        }
    });
}
