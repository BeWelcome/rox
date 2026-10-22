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
    const closeDrawer = () => {
        contactDrawer.classList.remove('is-open');
        contactDrawer.setAttribute('aria-hidden', 'true');
        backdrop.hidden = true;
        contact.close(false);
    };
    const contact = createContactPanel(contactDrawer.querySelector('.js-search-contact'), {
        from,
        onBack: closeDrawer,
    });
    onContactLinkClick('.js-contact-open', (link) => {
        contact.open(link);
        contactDrawer.classList.add('is-open');
        contactDrawer.setAttribute('aria-hidden', 'false');
        backdrop.hidden = false;
    });
    backdrop.addEventListener('click', closeDrawer);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && contactDrawer.classList.contains('is-open')) {
            closeDrawer();
        }
    });
}
