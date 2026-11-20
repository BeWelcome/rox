/*
 * The words of the inbox search (?q=, the field above the list) shown in the list and in the open conversation,
 * as the search finds them: case aside, at the start of a word ("berl" in "Berlin").
 */
const PARTS = [
    '.c-inbox__list .c-inbox-item__name',
    '.c-inbox__list .c-inbox-item__subject',
    '.c-inbox__list .c-inbox-item__preview',
    '.c-inbox__list .c-inbox-item__snippet-text',
    '.c-inbox__pane .c-bubble__text',
    '.c-inbox__pane .c-inbox-head__subject',
];

function searchWords() {
    const field = document.querySelector('.c-inbox-search__field');
    const value = field ? field.value : '';
    // The signs the search leaves out split words, as on the server (MessageSearch::queryString())
    return value
        .replace(/[\\()|\-!@~"&/^$=<>*?%'.,;:+[\]{}]+/gu, ' ')
        .split(/\s+/u)
        .filter((word) => word !== '');
}

function pattern(words) {
    const escaped = words
        .sort((a, b) => b.length - a.length)
        .map((word) => word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));

    return new RegExp('(^|[^\\p{L}\\p{N}])(' + escaped.join('|') + ')', 'giu');
}

function markText(node, regexp) {
    const text = node.nodeValue;
    regexp.lastIndex = 0;
    let match = regexp.exec(text);
    if (!match) {
        return;
    }
    const fragment = document.createDocumentFragment();
    let last = 0;
    while (match) {
        const start = match.index + match[1].length;
        fragment.append(text.slice(last, start));
        const mark = document.createElement('mark');
        mark.className = 'c-inbox-mark';
        mark.textContent = match[2];
        fragment.append(mark);
        last = start + match[2].length;
        match = regexp.exec(text);
    }
    fragment.append(text.slice(last));
    node.replaceWith(fragment);
}

/**
 * Marks the words in the list and the open conversation; runs again when the pane shows another conversation
 * or the search brings a new list.
 */
export function highlightSearch() {
    // The marks of an earlier search (the open conversation stays when the list is searched again)
    document.querySelectorAll('.c-inbox-mark').forEach((mark) => {
        const parent = mark.parentNode;
        mark.replaceWith(mark.textContent);
        parent.normalize();
    });

    const words = searchWords();
    if (words.length === 0) {
        return;
    }
    const regexp = pattern(words);
    document.querySelectorAll(PARTS.join(',')).forEach((part) => {
        const walker = document.createTreeWalker(part, NodeFilter.SHOW_TEXT);
        const nodes = [];
        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }
        nodes.forEach((node) => markText(node, regexp));
    });

    // The first message with the words comes into view
    const first = document.querySelector('.c-inbox__pane .c-bubble__text .c-inbox-mark');
    if (first) {
        first.scrollIntoView({ block: 'center' });
    }
}
