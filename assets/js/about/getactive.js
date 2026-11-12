/*
 * Get active (templates/about/getactive.html.twig): the pills show the teams of one group, or all. Without
 * JavaScript all teams show.
 */
document.querySelectorAll('.js-getactive').forEach((page) => {
    const pills = [...page.querySelectorAll('.c-pill[data-group]')];
    const tiles = [...page.querySelectorAll('.p-getactive__tile[data-group]')];

    pills.forEach((pill) => {
        pill.addEventListener('click', () => {
            const group = pill.dataset.group;
            pills.forEach((other) => {
                const active = other === pill;
                other.classList.toggle('is-active', active);
                other.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
            tiles.forEach((tile) => {
                tile.hidden = '' !== group && tile.dataset.group !== group;
            });
        });
    });
});
