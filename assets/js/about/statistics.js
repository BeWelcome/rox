/*
 * The statistics (templates/about/statistics.html.twig): each figure's data by week and by day is loaded at once;
 * the card shows the latest value and an area chart of the chosen period. The lists (logins, countries, languages)
 * become bars scaled to their largest row, with each row's share of all.
 */
import Chart from 'chart.js/auto';

const page = document.querySelector('.js-stats');

const load = (url) => fetch(url).then((response) => {
    if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
    }

    return response.json();
});

if (page) {
    const locale = page.dataset.locale || undefined;
    const numbers = new Intl.NumberFormat(locale);
    const dates = {
        weekly: new Intl.DateTimeFormat(locale, {month: 'short', year: 'numeric'}),
        daily: new Intl.DateTimeFormat(locale, {day: 'numeric', month: 'short'}),
    };
    let period = 'weekly';
    const metrics = [];

    const show = (metric) => {
        const data = metric.data[period];
        if (!data) {
            return;
        }
        const last = data.numbers.length > 0 ? data.numbers[data.numbers.length - 1] : null;
        metric.value.textContent = null === last ? '–' : numbers.format(last);
        metric.from.textContent = data.labels.length > 0 ? dates[period].format(new Date(data.labels[0])) : '';
        metric.to.textContent = data.labels.length > 0 ? dates[period].format(new Date(data.labels[data.labels.length - 1])) : '';
        metric.chart.data.labels = data.labels;
        metric.chart.data.datasets[0].data = data.numbers;
        metric.chart.update('none');
    };

    page.querySelectorAll('.js-stats-metric').forEach((card) => {
        const metric = {
            value: card.querySelector('.js-stats-value'),
            from: card.querySelector('.js-stats-from'),
            to: card.querySelector('.js-stats-to'),
            data: {},
            chart: new Chart(card.querySelector('.js-stats-chart'), {
                type: 'line',
                data: {labels: [], datasets: [{
                    data: [],
                    backgroundColor: '#fdebdc',
                    borderColor: '#ea7a2e',
                    borderWidth: 2,
                    fill: true,
                    pointRadius: 0,
                    tension: 0.25,
                }]},
                options: {
                    animation: false,
                    maintainAspectRatio: false,
                    responsive: true,
                    plugins: {legend: {display: false}, tooltip: {intersect: false, mode: 'index'}},
                    scales: {x: {display: false}, y: {display: false, beginAtZero: false}},
                },
            }),
        };
        metrics.push(metric);
        ['weekly', 'daily'].forEach((key) => {
            load(card.dataset[key]).then((data) => {
                metric.data[key] = {labels: data.labels, numbers: data.numbers.map(Number)};
                if (key === period) {
                    show(metric);
                }
            }).catch(() => {});
        });
    });

    const buttons = [...page.querySelectorAll('[data-period]')];
    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            period = button.dataset.period;
            buttons.forEach((other) => {
                const active = other === button;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            metrics.forEach(show);
        });
    });

    page.querySelectorAll('.js-stats-list').forEach((card) => {
        const list = card.querySelector('.js-stats-rows');
        load(card.dataset.url).then((data) => {
            const rows = Object.entries(data).map(([label, count]) => [label, Number(count)]);
            const total = rows.reduce((sum, [, count]) => sum + count, 0);
            const largest = Math.max(1, ...rows.map(([, count]) => count));
            list.replaceChildren();
            rows.forEach(([label, count]) => {
                const row = document.createElement('li');
                row.className = 'p-stats__row';
                const name = document.createElement('span');
                name.className = 'p-stats__label';
                name.textContent = label;
                name.title = label;
                const track = document.createElement('span');
                track.className = 'p-stats__track';
                const fill = document.createElement('span');
                fill.className = 'p-stats__fill';
                fill.style.display = 'block';
                fill.style.width = `${(count / largest) * 100}%`;
                track.append(fill);
                const share = document.createElement('span');
                share.className = 'p-stats__share';
                share.textContent = `${total > 0 ? Math.round((count / total) * 100) : 0}%`;
                row.append(name, track, share);
                list.append(row);
            });
        }).catch(() => {});
    });
}
