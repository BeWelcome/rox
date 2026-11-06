/*
 * The FAQ (templates/faq/faq.html.twig): the questions are <details>; a link to #question12 (also when the hash
 * changes on the page) opens that question and scrolls to it.
 */
function openHash() {
    const id = window.location.hash.slice(1);
    if (id === '') {
        return;
    }
    const item = document.getElementById(id);
    if (item && item.tagName === 'DETAILS') {
        item.open = true;
        item.scrollIntoView({block: 'start'});
    }
}

openHash();
window.addEventListener('hashchange', openHash);
