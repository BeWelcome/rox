/*
 * The dark bubble above a request's date pill in the conversation lists
 * (templates/conversations/_request_state.html.twig), on a computer only: the kind and where it
 * stands, the full dates, the nights and the people. Placed in the page (fixed), so the
 * scrolling list never cuts it; below the pill when there is no room above.
 */
const canHover = window.matchMedia('(hover: hover) and (min-width: 768px)');
let tip = null;

function bubble() {
    if (!tip) {
        tip = document.createElement('div');
        tip.className = 'c-req-tip';
        tip.setAttribute('aria-hidden', 'true');
        document.body.appendChild(tip);
    }
    return tip;
}

function line(className, text) {
    const element = document.createElement('span');
    element.className = className;
    element.textContent = text;
    return element;
}

function show(pill) {
    const box = bubble();
    box.replaceChildren(
        line('c-req-tip__title', pill.dataset.tipTitle || ''),
        line('c-req-tip__dates', pill.dataset.tipDates || ''),
        line('c-req-tip__stay', pill.dataset.tipStay || ''),
    );
    box.className = 'c-req-tip ' + [...pill.classList].filter((name) => name.startsWith('c-req-pill--')).map((name) => name.replace('c-req-pill--', 'c-req-tip--')).join(' ');
    box.style.visibility = 'hidden';
    box.classList.add('is-shown');

    const target = pill.getBoundingClientRect();
    const size = box.getBoundingClientRect();
    const gap = 8;
    const below = target.top - size.height - gap < 8;
    const top = below ? target.bottom + gap : target.top - size.height - gap;
    const left = Math.min(Math.max(8, target.left + target.width / 2 - size.width / 2), window.innerWidth - size.width - 8);
    box.style.top = `${top}px`;
    box.style.left = `${left}px`;
    box.classList.toggle('is-below', below);
    box.style.visibility = '';
}

function hide() {
    if (tip) {
        tip.classList.remove('is-shown');
    }
}

document.addEventListener('mouseover', (event) => {
    if (!canHover.matches) {
        return;
    }
    const pill = event.target.closest('.c-req-pill[data-tip-title]');
    if (pill && !pill.contains(event.relatedTarget)) {
        show(pill);
    }
});
document.addEventListener('mouseout', (event) => {
    const pill = event.target.closest('.c-req-pill[data-tip-title]');
    if (pill && !pill.contains(event.relatedTarget)) {
        hide();
    }
});
window.addEventListener('scroll', hide, true);
