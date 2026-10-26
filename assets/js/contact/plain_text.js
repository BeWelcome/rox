/*
 * Plain text box for a new message or stay request (no rich editor): it grows with the
 * text, and on submit the text is sent as paragraphs, since messages are stored as HTML
 * (like the reply box under a conversation). data-html holds the text sent back after a
 * form error, as HTML, to show it again as plain text.
 */
const escapeHtml = (text) => text
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');

const toHtml = (text) => text
    .trim()
    .split(/\n\s*\n/)
    .map((paragraph) => '<p>' + escapeHtml(paragraph.trim()).replace(/\n/g, '<br>') + '</p>')
    .join('');

function toText(html) {
    if (!html || !html.trim()) {
        return '';
    }
    const doc = new DOMParser().parseFromString(html, 'text/html');
    doc.querySelectorAll('br').forEach((br) => br.replaceWith('\n'));
    const blocks = Array.from(doc.body.querySelectorAll('p, li, blockquote'));
    if (blocks.length === 0) {
        return doc.body.textContent.trim();
    }
    return blocks.map((block) => block.textContent.trim()).filter((text) => text !== '').join('\n\n');
}

export function initPlainTextFields() {
    document.querySelectorAll('textarea.js-plain-message').forEach((textarea) => {
        textarea.value = toText(textarea.dataset.html);
        const minHeight = textarea.offsetHeight;
        const resize = () => {
            textarea.style.height = 'auto';
            textarea.style.height = Math.max(minHeight, textarea.scrollHeight) + 'px';
        };
        textarea.addEventListener('input', resize);
        resize();

        const form = textarea.form;
        form.addEventListener('submit', () => {
            if (textarea.value.trim() === '') {
                return;
            }
            const html = document.createElement('input');
            html.type = 'hidden';
            html.name = textarea.name;
            html.value = toHtml(textarea.value);
            textarea.removeAttribute('name');
            form.appendChild(html);
        });
    });
}
