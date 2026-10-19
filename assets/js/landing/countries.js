// "Explore by country" on the home page (templates/landing/_countries.html.twig):
// continent tabs, pages of countries, and a field to find a country on any continent.

function pageSize() {
    if (window.matchMedia('(min-width: 992px)').matches) {
        return 8;
    }
    return window.matchMedia('(min-width: 640px)').matches ? 6 : 5;
}

function normalize(text) {
    return text.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().trim();
}

function initExplore(root) {
    const tabs = Array.from(root.querySelectorAll('.js-places-tab'));
    const items = Array.from(root.querySelectorAll('.js-places-item'));
    const search = root.querySelector('.js-places-search');
    const summary = root.querySelector('.js-places-summary');
    const empty = root.querySelector('.js-places-empty');
    const pager = root.querySelector('.js-places-pager');
    const dots = root.querySelector('.js-places-dots');
    const pageLabel = root.querySelector('.js-places-page');
    const prev = root.querySelector('.js-places-prev');
    const next = root.querySelector('.js-places-next');

    items.forEach(item => { item.dataset.search = normalize(item.dataset.name); });

    let continent = (tabs.find(tab => tab.classList.contains('is-active')) || tabs[0])?.dataset.continent;
    let page = 0;

    function matching() {
        const query = normalize(search.value);
        if (query) {
            return items.filter(item => item.dataset.search.includes(query));
        }
        return items.filter(item => item.dataset.continent === continent);
    }

    function render() {
        const list = matching();
        const size = pageSize();
        const pages = Math.max(1, Math.ceil(list.length / size));
        page = Math.min(page, pages - 1);
        const shown = new Set(list.slice(page * size, (page + 1) * size));

        items.forEach(item => { item.hidden = !shown.has(item); });
        empty.hidden = list.length > 0;

        const searching = normalize(search.value) !== '';
        root.classList.toggle('is-searching', searching);
        const activeTab = tabs.find(tab => tab.dataset.continent === continent);
        summary.textContent = !searching && activeTab ? activeTab.dataset.summary : '';

        pager.hidden = pages < 2;
        pageLabel.textContent = (page + 1) + ' / ' + pages;
        prev.disabled = page === 0;
        next.disabled = page >= pages - 1;
        const dotCount = Math.min(pages, 6);
        const current = dotCount > 1 ? Math.round(page / (pages - 1) * (dotCount - 1)) : 0;
        dots.replaceChildren(...Array.from({ length: dotCount }, (_, index) => {
            const dot = document.createElement('span');
            dot.className = 'c-places-explore__dot' + (index === current ? ' is-active' : '');
            return dot;
        }));
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            continent = tab.dataset.continent;
            page = 0;
            search.value = '';
            tabs.forEach(other => {
                const active = other === tab;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-selected', active ? 'true' : 'false');
            });
            render();
        });
    });
    search.addEventListener('input', () => { page = 0; render(); });
    search.addEventListener('keydown', event => {
        // Enter opens the first country found.
        if (event.key === 'Enter') {
            event.preventDefault();
            const first = matching()[0];
            if (first) {
                window.location.href = first.querySelector('a').href;
            }
        }
    });
    prev.addEventListener('click', () => { page -= 1; render(); });
    next.addEventListener('click', () => { page += 1; render(); });

    let lastSize = pageSize();
    window.addEventListener('resize', () => {
        const size = pageSize();
        if (size !== lastSize) {
            page = Math.floor(page * lastSize / size);
            lastSize = size;
            render();
        }
    });

    render();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => document.querySelectorAll('.js-places-explore').forEach(initExplore));
} else {
    document.querySelectorAll('.js-places-explore').forEach(initExplore);
}
