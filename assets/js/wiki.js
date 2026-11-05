/*
 * The wiki (templates/wiki, templates/group/wiki*.html.twig):
 * - "On this page" marks the section read and scrolls to a section below the top bar;
 * - the search box suggests pages as one types (Enter opens the first, Esc clears);
 * - the editor switches between the visual mode and the markup, with the toolbar for both and the categories as
 *   chips in the visual mode; the form always sends markup.
 */
import { htmlToMarkup, isVisualEditable, markupToHtml, splitCategories, withCategories } from './wiki/markup';

const TOP_OFFSET = 88;
const SPY_LINE = 130;

// On this page
const toc = document.querySelector('[data-wiki-toc]');
if (toc) {
    const links = [...toc.querySelectorAll('a[href^="#"]')];
    const headings = links.map((link) => document.getElementById(decodeURIComponent(link.hash.slice(1)))).filter(Boolean);
    const mark = () => {
        let current = headings[0];
        headings.forEach((heading) => {
            if (heading.getBoundingClientRect().top <= SPY_LINE) {
                current = heading;
            }
        });
        links.forEach((link) => link.parentElement.classList.toggle('is-active', current && link.hash === '#' + current.id));
    };
    links.forEach((link) => link.addEventListener('click', (event) => {
        const target = document.getElementById(decodeURIComponent(link.hash.slice(1)));
        if (!target) {
            return;
        }
        event.preventDefault();
        window.scrollTo({ top: target.getBoundingClientRect().top + window.scrollY - TOP_OFFSET, behavior: 'smooth' });
        history.replaceState(null, '', link.hash);
    }));
    window.addEventListener('scroll', mark, { passive: true });
    mark();
}

// The search box
document.querySelectorAll('[data-wiki-search]').forEach((form) => {
    const input = form.querySelector('input[name="q"]');
    const box = form.querySelector('.c-wiki-search__results');
    let timer = null;
    let request = 0;
    let options = [];
    let active = -1;

    const close = () => {
        box.hidden = true;
        box.replaceChildren();
        input.setAttribute('aria-expanded', 'false');
        options = [];
        active = -1;
    };
    const highlight = (index) => {
        active = index;
        options.forEach((option, i) => option.classList.toggle('is-active', i === index));
    };
    const option = (url, title, text, extraClass) => {
        const link = document.createElement('a');
        link.className = 'c-wiki-search__result' + (extraClass ? ' ' + extraClass : '');
        link.href = url;
        link.setAttribute('role', 'option');
        const strong = document.createElement('strong');
        strong.textContent = title;
        link.append(strong);
        if (text) {
            const line = document.createElement('span');
            line.textContent = text;
            link.append(line);
        }
        return link;
    };
    const show = (query, data) => {
        box.replaceChildren();
        options = [];
        if (data.results.length === 0) {
            const none = document.createElement('p');
            none.className = 'c-wiki-search__none';
            none.textContent = input.dataset.none.replace('__QUERY__', query);
            box.append(none);
        }
        data.results.forEach((result) => {
            const link = option(result.url, result.title, result.snippet);
            box.append(link);
            options.push(link);
        });
        if (data.create) {
            const create = option(data.create, '+ ' + input.dataset.create.replace('__QUERY__', query), '', 'c-wiki-search__create');
            box.append(create);
            options.push(create);
        }
        box.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        highlight(options.length ? 0 : -1);
    };
    const suggest = () => {
        const query = input.value.trim();
        if (query === '') {
            close();
            return;
        }
        const current = ++request;
        fetch(form.dataset.wikiSearch + '?q=' + encodeURIComponent(query), { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((data) => { if (current === request) { show(query, data); } })
            .catch(close);
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(suggest, 200);
    });
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            input.value = '';
            close();
        } else if (event.key === 'ArrowDown' && options.length) {
            event.preventDefault();
            highlight((active + 1) % options.length);
        } else if (event.key === 'ArrowUp' && options.length) {
            event.preventDefault();
            highlight((active - 1 + options.length) % options.length);
        } else if (event.key === 'Enter' && active >= 0 && !box.hidden) {
            event.preventDefault();
            window.location.href = options[active].href;
        }
    });
    document.addEventListener('click', (event) => {
        if (!form.contains(event.target)) {
            close();
        }
    });
});

// The editor
const editor = document.querySelector('[data-wiki-editor]');
if (editor) {
    const textarea = editor.querySelector('[data-wiki-markup]');
    const visual = editor.querySelector('[data-wiki-visual]');
    const modes = editor.querySelector('[data-wiki-modes]');
    const visualButton = modes.querySelector('[data-wiki-mode="visual"]');
    const categoryBox = editor.querySelector('[data-wiki-categories]');
    const chips = editor.querySelector('[data-wiki-chips]');
    const categoryInput = editor.querySelector('[data-wiki-category-input]');
    let mode = 'markup';
    let categories = [];

    const renderChips = () => {
        if (!chips) {
            return;
        }
        chips.replaceChildren(...categories.map((name) => {
            const chip = document.createElement('li');
            chip.className = 'c-wiki-chip';
            chip.textContent = name;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.setAttribute('aria-label', chips.dataset.remove.replace('__CATEGORY__', name));
            remove.innerHTML = '<i class="fas fa-xmark" aria-hidden="true"></i>';
            remove.addEventListener('click', () => {
                categories = categories.filter((known) => known !== name);
                renderChips();
            });
            chip.append(remove);
            return chip;
        }));
    };
    const addCategory = () => {
        const name = categoryInput.value.replace(/,/g, '').replace(/[[\]|]/g, '').trim();
        if (name !== '' && !categories.some((known) => known.toLowerCase() === name.toLowerCase())) {
            categories.push(name);
            renderChips();
        }
        categoryInput.value = '';
    };
    const markup = () => (mode === 'visual' ? withCategories(htmlToMarkup(visual), categories) : textarea.value);

    const setMode = (next) => {
        if (next === 'visual' && !isVisualEditable(textarea.value)) {
            next = 'markup';
        }
        if (next === 'visual' && mode !== 'visual') {
            const split = splitCategories(textarea.value);
            categories = split.categories;
            renderChips();
            visual.innerHTML = markupToHtml(split.text);
        } else if (next === 'markup' && mode === 'visual') {
            textarea.value = markup();
        }
        mode = next;
        visual.hidden = mode !== 'visual';
        textarea.hidden = mode === 'visual';
        if (categoryBox) {
            categoryBox.hidden = mode !== 'visual';
        }
        modes.querySelectorAll('[data-wiki-mode]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.wikiMode === mode);
            button.setAttribute('aria-pressed', String(button.dataset.wikiMode === mode));
        });
    };

    // the markup's toolbar: wraps the selection or marks its lines
    const editMarkup = (cmd) => {
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const value = textarea.value;
        const selected = value.slice(start, end);
        let next = value;
        let selectFrom = start;
        let selectTo = end;
        const wrap = (before, after, placeholder) => {
            const text = selected || placeholder;
            next = value.slice(0, start) + before + text + after + value.slice(end);
            selectFrom = start + before.length;
            selectTo = selectFrom + text.length;
        };
        const lines = (change) => {
            const lineStart = value.lastIndexOf('\n', start - 1) + 1;
            let lineEnd = value.indexOf('\n', end);
            if (lineEnd < 0) {
                lineEnd = value.length;
            }
            const block = value.slice(lineStart, lineEnd).split('\n').map((line) => change(line.replace(/^=+\s*|\s*=+$/g, '').replace(/^[*#]\s*/, ''))).join('\n');
            next = value.slice(0, lineStart) + block + value.slice(lineEnd);
            selectFrom = lineStart;
            selectTo = lineStart + block.length;
        };
        // bold (''') and italic ('') toggle: marks in or right around the selection go, else they wrap it;
        // ''''' is both, so taking one leaves the other
        const toggle = (mark, placeholder) => {
            const size = mark.length;
            const has = (count) => (size === 3 ? count === 3 || count >= 5 : count === 2 || count >= 5);
            const lead = (text) => text.match(/^'*/)[0].length;
            const trail = (text) => text.match(/'*$/)[0].length;
            const inner = selected.slice(size, -size);
            const markInside = size === 3 ? inner.includes("'''") : /(^|[^'])''([^']|$)/.test(inner);
            if (selected.length > 2 * size && has(lead(selected)) && has(trail(selected)) && !markInside) {
                next = value.slice(0, start) + inner + value.slice(end);
                selectTo = start + inner.length;
            } else if (has(trail(value.slice(0, start))) && has(lead(value.slice(end)))) {
                next = value.slice(0, start - size) + selected + value.slice(end + size);
                selectFrom = start - size;
                selectTo = end - size;
            } else {
                wrap(mark, mark, placeholder);
            }
        };
        switch (cmd) {
            case 'bold': toggle("'''", 'bold'); break;
            case 'italic': toggle("''", 'italic'); break;
            case 'h2': lines((line) => '== ' + line + ' =='); break;
            case 'h3': lines((line) => '=== ' + line + ' ==='); break;
            case 'paragraph': lines((line) => line); break;
            case 'bullets': lines((line) => '* ' + line); break;
            case 'numbers': lines((line) => '# ' + line); break;
            case 'link': {
                const page = selected || window.prompt(editor.dataset.linkPrompt);
                if (!page) {
                    return;
                }
                next = value.slice(0, start) + '[[' + page + ']]' + value.slice(end);
                selectFrom = start;
                selectTo = start + page.length + 4;
                break;
            }
            default: return;
        }
        textarea.value = next;
        textarea.focus();
        textarea.setSelectionRange(selectFrom, selectTo);
        textarea.dispatchEvent(new Event('input'));
    };

    // the visual mode's toolbar
    const editVisual = (cmd) => {
        visual.focus();
        switch (cmd) {
            case 'bold': document.execCommand('bold'); break;
            case 'italic': document.execCommand('italic'); break;
            case 'h2': document.execCommand('formatBlock', false, '<h2>'); break;
            case 'h3': document.execCommand('formatBlock', false, '<h3>'); break;
            case 'paragraph': document.execCommand('formatBlock', false, '<p>'); break;
            case 'bullets': document.execCommand('insertUnorderedList'); break;
            case 'numbers': document.execCommand('insertOrderedList'); break;
            case 'link': {
                const selected = window.getSelection().toString();
                const page = window.prompt(editor.dataset.linkPrompt, selected);
                if (!page) {
                    return;
                }
                const link = document.createElement('a');
                link.href = '#';
                link.dataset.page = page.trim();
                link.textContent = selected || page.trim();
                document.execCommand('insertHTML', false, link.outerHTML);
                break;
            }
            default:
        }
    };

    modes.hidden = false;
    editor.querySelector('[data-wiki-tools]').hidden = false;
    modes.querySelectorAll('[data-wiki-mode]').forEach((button) => button.addEventListener('click', () => setMode(button.dataset.wikiMode)));
    editor.querySelectorAll('[data-wiki-cmd]').forEach((button) => {
        // keeps the selection of the visual mode
        button.addEventListener('mousedown', (event) => event.preventDefault());
        button.addEventListener('click', () => (mode === 'visual' ? editVisual(button.dataset.wikiCmd) : editMarkup(button.dataset.wikiCmd)));
    });
    visual.addEventListener('click', (event) => {
        if (event.target.closest('a')) {
            event.preventDefault();
        }
    });
    textarea.addEventListener('input', () => {
        const editable = isVisualEditable(textarea.value);
        visualButton.disabled = !editable;
    });
    if (categoryInput) {
        categoryInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                addCategory();
            }
        });
        categoryInput.addEventListener('change', addCategory);
    }
    editor.addEventListener('submit', () => {
        textarea.value = markup();
    });

    setMode(editor.dataset.wikiEditor === 'visual' ? 'visual' : 'markup');
}
