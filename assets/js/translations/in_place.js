import {createContactPanel, onContactLinkClick} from '../contact/panel';

/*
 * Translation mode (templates/admin/translations/_in_place.html.twig): the texts the page translated are listed
 * below it. Edit, Translate and Create open their page in a side panel (?embed=1). Once saved, that page posts
 * "bw-translation-saved"; the texts are rendered again with the parameters the page used
 * (translation_render) and replace the old ones on the page where they can be found: text, attributes like
 * placeholder and title, and elements whose HTML is the text. The list shows the new text and offers Edit.
 */
const IGNORED = 'script, style, noscript, template, textarea, .js-in-place, .js-translation-drawer';
const ATTRIBUTES = ['placeholder', 'title', 'aria-label', 'alt', 'value', 'data-title'];
const SHOWN = 160;

const normalize = (text) => String(text).replace(/\s+/g, ' ').trim();

function shownText(text) {
    const element = document.createElement('div');
    element.innerHTML = text;
    const plain = normalize(element.textContent);

    return plain.length > SHOWN ? plain.slice(0, SHOWN) + '…' : plain;
}

function isIgnored(node) {
    const element = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;

    return !element || element.closest(IGNORED) !== null;
}

function mark(element) {
    if (!element) {
        return;
    }
    element.classList.remove('is-translation-updated');
    // Restarts the highlight
    void element.offsetWidth;
    element.classList.add('is-translation-updated');
}

/*
 * Replaces the old text by the new one on the page; returns how often it did.
 */
export function replaceOnPage(root, oldText, newText) {
    const old = normalize(oldText);
    if (old === '' || old === normalize(newText)) {
        return 0;
    }
    let replaced = 0;

    // Elements whose HTML is the text (printed with |raw)
    if (/<[a-z][^>]*>/i.test(oldText)) {
        const parsed = document.createElement('template');
        parsed.innerHTML = oldText;
        const oldHtml = normalize(parsed.innerHTML);
        root.querySelectorAll('*').forEach((element) => {
            if (!isIgnored(element) && element.children.length > 0 && normalize(element.innerHTML) === oldHtml) {
                element.innerHTML = newText;
                mark(element);
                replaced++;
            }
        });
    }

    // Text: the whole text of a node first; else where it is part of one (a label before a value)
    const nodes = [];
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    while (walker.nextNode()) {
        if (!isIgnored(walker.currentNode)) {
            nodes.push(walker.currentNode);
        }
    }
    const whole = nodes.filter((node) => normalize(node.nodeValue) === old);
    const replaceIn = whole.length > 0 || old.length < 4 ? whole : nodes.filter((node) => node.nodeValue.includes(oldText));
    replaceIn.forEach((node) => {
        if (whole.length > 0) {
            const [, before, after] = node.nodeValue.match(/^(\s*)[\s\S]*?(\s*)$/);
            node.nodeValue = before + newText + after;
        } else {
            node.nodeValue = node.nodeValue.split(oldText).join(newText);
        }
        mark(node.parentElement);
        replaced++;
    });

    // Attributes
    root.querySelectorAll(ATTRIBUTES.map((name) => `[${name}]`).join(',')).forEach((element) => {
        if (isIgnored(element)) {
            return;
        }
        ATTRIBUTES.forEach((name) => {
            if (element.hasAttribute(name) && normalize(element.getAttribute(name)) === old) {
                element.setAttribute(name, newText);
                if (name === 'value' && 'value' in element) {
                    element.value = newText;
                }
                mark(element);
                replaced++;
            }
        });
    });

    return replaced;
}

async function render(url, code, locale, parameterSets) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
        body: JSON.stringify({code, locale, parameterSets}),
        credentials: 'same-origin',
    });
    if (!response.ok) {
        throw new Error(`translation_render: ${response.status}`);
    }

    return (await response.json()).texts;
}

async function showSaved(list, item) {
    const occurrences = JSON.parse(item.dataset.occurrences || '[]');
    // Texts rendered with parameters that can't be sent back (objects) can't be replaced
    const known = occurrences.filter((occurrence) => occurrence.parameters !== null);
    if (known.length === 0) {
        return;
    }
    const texts = await render(list.dataset.renderUrl, item.dataset.code, item.dataset.locale,
        known.map((occurrence) => (Array.isArray(occurrence.parameters) ? {} : occurrence.parameters)));
    known.forEach((occurrence, index) => {
        replaceOnPage(document.head, occurrence.text, texts[index]);
        replaceOnPage(document.body, occurrence.text, texts[index]);
        occurrence.text = texts[index];
    });
    item.dataset.occurrences = JSON.stringify(occurrences);
    const text = item.querySelector('.js-in-place-text');
    if (text) {
        text.textContent = shownText(texts[0]);
        mark(text);
    }
}

function offerEdit(item) {
    const link = item.querySelector('.js-translation-open');
    if (!link || !link.dataset.editUrl) {
        return;
    }
    link.href = link.dataset.editUrl;
    link.replaceChildren();
    const icon = document.createElement('i');
    icon.className = 'fas fa-pen';
    icon.setAttribute('aria-hidden', 'true');
    link.append(icon, ' ', link.dataset.editLabel);
}

export function initInPlaceTranslations() {
    const list = document.querySelector('.js-in-place');
    const drawer = document.querySelector('.js-translation-drawer');
    if (!list || !drawer) {
        return;
    }
    const backdrop = document.querySelector('.js-translation-drawer-close');
    const frame = drawer.querySelector('.js-search-contact-frame');
    let current = null;

    const close = () => {
        drawer.classList.remove('is-open');
        drawer.setAttribute('aria-hidden', 'true');
        backdrop.hidden = true;
        panel.close(false);
        if (current) {
            current.classList.remove('is-active');
        }
    };
    const panel = createContactPanel(drawer.querySelector('.js-search-contact'), {from: 'translation', onBack: close});

    onContactLinkClick('.js-translation-open', (link) => {
        if (current) {
            current.classList.remove('is-active');
        }
        current = link.closest('.js-in-place-item');
        current.classList.add('is-active');
        panel.open(link);
        drawer.classList.add('is-open');
        drawer.setAttribute('aria-hidden', 'false');
        backdrop.hidden = false;
    });
    backdrop.addEventListener('click', close);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && drawer.classList.contains('is-open')) {
            close();
        }
    });

    window.addEventListener('message', (event) => {
        if (event.origin !== window.location.origin || event.source !== frame.contentWindow
            || !event.data || event.data.type !== 'bw-translation-saved') {
            return;
        }
        const item = current;
        close();
        if (!item) {
            return;
        }
        offerEdit(item);
        showSaved(list, item).catch(() => {
            // The new text shows after the next reload
            item.classList.add('is-reload-needed');
        });
    });
}

initInPlaceTranslations();
