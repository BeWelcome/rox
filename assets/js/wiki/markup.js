/*
 * The visual editor's conversion between the toolbar's wiki markup and HTML: headings, bold, italic, links, flat
 * lists, definition lists (; term, : description), rules and paragraphs. Pages with more than that open as markup:
 * UNSUPPORTED checks the same as WikiParser::VISUAL_BLOCKERS (src/Wiki/WikiParser.php).
 */
const UNSUPPORTED = /\{\{|\{\||<\/?[a-z][^>]*>|<!--|__[A-Z]+__|^#redirect|^[ \t]+\S|^[*#]{2,}|^[*#]+[;:]|^[;:][;:*#]|^={5,}|\[\[\s*(?:image|file|media)\s*:/mi;
const CATEGORY = /^[ \t]*\[\[\s*category\s*:\s*([^\]|]+?)\s*(?:\|[^\]]*)?\]\][ \t]*(?:\n|$)|\[\[\s*category\s*:\s*([^\]|]+?)\s*(?:\|[^\]]*)?\]\]/gim;
const LINKS = /\[\[([^[\]|]+)(?:\|([^[\]]*))?\]\]|\[((?:https?|ftp):\/\/[^\s[\]<>]+|mailto:[^\s[\]<>]+)(?:\s+([^\]]*))?\]/gi;
const BARE_URL = /(^|[^\w/="'])((?:https?|ftp):\/\/[^\s<>[\]"\u0001\u0002]+?)(?=[.,;:!?)]*(?:\s|$|<|\u0001))/gi;

export function isVisualEditable(markup) {
    return !UNSUPPORTED.test(markup.replace(/\r/g, ''));
}

/** the markup without its [[Category:…]], and the categories */
export function splitCategories(markup) {
    const categories = [];
    const text = markup.replace(/\r/g, '').replace(CATEGORY, (match, line, inline) => {
        const name = (line || inline).replace(/_/g, ' ');
        if (!categories.some((known) => known.toLowerCase() === name.toLowerCase())) {
            categories.push(name);
        }
        return '';
    });
    return { text: text.replace(/\n{3,}/g, '\n\n').trim(), categories };
}

export function withCategories(markup, categories) {
    const lines = categories.map((name) => '[[Category:' + name + ']]');
    return [markup.trim(), lines.join('\n')].filter((part) => part !== '').join('\n\n');
}

const escape = (text) => text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
const emphasis = (html) => html
    .replace(/'''''(.+?)'''''/g, '<strong><em>$1</em></strong>')
    .replace(/'''(.+?)'''/g, '<strong>$1</strong>')
    .replace(/''(.+?)''/g, '<em>$1</em>');

function inline(text) {
    const kept = [];
    const keep = (html) => { kept.push(html); return '\u0001' + (kept.length - 1) + '\u0002'; };
    let html = text.replace(LINKS, (match, page, caption, url, label) => {
        if (page !== undefined) {
            const target = page.trim();
            return keep('<a href="#" data-page="' + escape(target) + '">' + emphasis(escape((caption || '').trim() || target)) + '</a>');
        }
        return keep('<a href="' + escape(url) + '">' + emphasis(escape((label || '').trim() || url)) + '</a>');
    });
    html = emphasis(escape(html));
    html = html.replace(BARE_URL, (match, before, url) => before + keep('<a href="' + url + '">' + url + '</a>'));
    return html.replace(/\u0001(\d+)\u0002/g, (match, index) => kept[Number(index)]);
}

/** HTML of the toolbar's markup (no categories: see splitCategories) */
export function markupToHtml(markup) {
    const out = [];
    let paragraph = [];
    let list = null;
    const flushParagraph = () => {
        if (paragraph.length) {
            out.push('<p>' + inline(paragraph.join(' ')) + '</p>');
            paragraph = [];
        }
    };
    const flushList = () => {
        if (list) {
            out.push('<' + list.tag + '>' + list.items.map((item) => '<' + item.tag + '>' + inline(item.text) + '</' + item.tag + '>').join('') + '</' + list.tag + '>');
            list = null;
        }
    };
    const addItem = (tag, itemTag, text) => {
        if (list && list.tag !== tag) {
            flushList();
        }
        list = list || { tag, items: [] };
        list.items.push({ tag: itemTag, text });
    };
    markup.replace(/\r/g, '').split('\n').forEach((line) => {
        const heading = line.match(/^(={1,4})\s*(.+?)\s*\1\s*$/);
        const item = line.match(/^([*#])\s*(.*)$/);
        const definition = line.match(/^([;:])\s*(.*)$/);
        if (line.trim() === '') {
            flushParagraph();
            flushList();
        } else if (heading) {
            flushParagraph();
            flushList();
            const level = Math.max(2, heading[1].length);
            out.push('<h' + level + '>' + inline(heading[2]) + '</h' + level + '>');
        } else if (/^-{4,}\s*$/.test(line)) {
            flushParagraph();
            flushList();
            out.push('<hr>');
        } else if (item) {
            flushParagraph();
            addItem(item[1] === '*' ? 'ul' : 'ol', 'li', item[2]);
        } else if (definition) {
            flushParagraph();
            // ; term : description on one line is a term and its description
            const inlineDescription = definition[1] === ';' ? definition[2].match(/^(.*?)\s:\s(.*)$/) : null;
            if (inlineDescription) {
                addItem('dl', 'dt', inlineDescription[1]);
                addItem('dl', 'dd', inlineDescription[2]);
            } else {
                addItem('dl', definition[1] === ';' ? 'dt' : 'dd', definition[2]);
            }
        } else {
            flushList();
            paragraph.push(line.trim());
        }
    });
    flushParagraph();
    flushList();
    return out.join('\n');
}

function inlineMarkup(node) {
    return [...node.childNodes].map((child) => {
        if (child.nodeType === Node.TEXT_NODE) {
            return child.textContent.replace(/\s+/g, ' ');
        }
        if (child.nodeType !== Node.ELEMENT_NODE) {
            return '';
        }
        const tag = child.tagName;
        const inner = inlineMarkup(child);
        if (tag === 'BR') {
            return ' ';
        }
        if (inner.trim() === '') {
            return inner;
        }
        if (tag === 'STRONG' || tag === 'B') {
            return "'''" + inner + "'''";
        }
        if (tag === 'EM' || tag === 'I') {
            return "''" + inner + "''";
        }
        if (tag === 'A') {
            const page = child.dataset.page;
            if (page) {
                const title = page.replace(/_/g, ' ');
                return inner.trim() === title ? '[[' + title + ']]' : '[[' + title + '|' + inner.trim() + ']]';
            }
            const href = child.getAttribute('href') || '';
            if (/^(?:https?|ftp):\/\/|^mailto:/i.test(href)) {
                return inner.trim() === href ? href : '[' + href + ' ' + inner.trim() + ']';
            }
        }
        return inner;
    }).join('');
}

/** the toolbar's markup of the visual editor's HTML */
export function htmlToMarkup(root) {
    const blocks = [];
    let loose = '';
    const flushLoose = () => {
        if (loose.trim() !== '') {
            blocks.push(loose.trim());
        }
        loose = '';
    };
    const walk = (parent) => {
        [...parent.childNodes].forEach((child) => {
            if (child.nodeType === Node.TEXT_NODE || (child.nodeType === Node.ELEMENT_NODE && /^(A|B|STRONG|I|EM|SPAN|BR|U|CODE)$/.test(child.tagName))) {
                loose += child.nodeType === Node.TEXT_NODE ? child.textContent.replace(/\s+/g, ' ') : inlineMarkup({ childNodes: [child] });
                return;
            }
            if (child.nodeType !== Node.ELEMENT_NODE) {
                return;
            }
            flushLoose();
            const tag = child.tagName;
            const heading = tag.match(/^H([1-6])$/);
            if (heading) {
                const marks = '='.repeat(Math.min(4, Math.max(2, Number(heading[1]))));
                const text = inlineMarkup(child).trim();
                if (text) {
                    blocks.push(marks + ' ' + text + ' ' + marks);
                }
            } else if (tag === 'UL' || tag === 'OL') {
                const mark = tag === 'UL' ? '*' : '#';
                const items = [...child.querySelectorAll('li')].map((li) => mark + ' ' + inlineMarkup(li).trim()).filter((line) => line.length > 2);
                if (items.length) {
                    blocks.push(items.join('\n'));
                }
            } else if (tag === 'DL') {
                const items = [...child.children].filter((item) => item.tagName === 'DT' || item.tagName === 'DD')
                    .map((item) => (item.tagName === 'DT' ? '; ' : ': ') + inlineMarkup(item).trim()).filter((line) => line.length > 2);
                if (items.length) {
                    blocks.push(items.join('\n'));
                }
            } else if (tag === 'HR') {
                blocks.push('----');
            } else if (child.querySelector('h1,h2,h3,h4,h5,h6,ul,ol,dl,p,div,hr')) {
                walk(child);
            } else {
                const text = inlineMarkup(child).trim();
                if (text) {
                    blocks.push(text);
                }
            }
        });
        flushLoose();
    };
    walk(root);
    return blocks.join('\n\n');
}
