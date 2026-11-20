/*
 * The upcoming stays in the top bar (layout/_stays_nav.html.twig): fetched again and swapped in place,
 * after an answer that the page itself didn't reload (the side panel, assets/js/contact/panel.js).
 */
export async function refreshStaysNav() {
    const item = document.querySelector('.js-topbar-stays');
    if (!item || !item.dataset.refresh) {
        return;
    }
    try {
        const response = await fetch(item.dataset.refresh, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!response.ok) {
            return;
        }
        const holder = document.createElement('ul');
        holder.innerHTML = (await response.text()).trim();
        const fresh = holder.querySelector('.js-topbar-stays');
        if (fresh) {
            item.replaceWith(fresh);
        }
    } catch (error) {
        // Not refreshed: the next page shows it
    }
}
