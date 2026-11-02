/*
 * Tabs that scroll sideways when they don't fit (phones), with arrows at the ends: [data-tabs-scroller] around
 * .p-hero__tabs and its [data-tabs-scroll] arrows (the profile, the comments tool of the volunteers).
 */
export function initTabsScrollers(root = document) {
    root.querySelectorAll('[data-tabs-scroller]').forEach(scroller => {
        const tabs = scroller.querySelector('.p-hero__tabs');
        const update = () => {
            const maxScroll = tabs.scrollWidth - tabs.clientWidth;
            scroller.classList.toggle('can-scroll-prev', tabs.scrollLeft > 1);
            scroller.classList.toggle('can-scroll-next', tabs.scrollLeft < maxScroll - 1);
        };

        scroller.querySelectorAll('[data-tabs-scroll]').forEach(arrow => {
            arrow.addEventListener('click', () => {
                tabs.scrollBy({left: Number(arrow.dataset.tabsScroll) * tabs.clientWidth * 0.7});
            });
        });

        tabs.addEventListener('scroll', update, {passive: true});
        window.addEventListener('resize', update);
        update();
    });
}
