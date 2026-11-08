import {hasPhotos, initBubblePhotos, initMessagePhotos, photosHtml} from './contact/photos';
import {sendInPlace} from './conversation-send';
import {initRequests} from './request-page';
import {initInboxNav} from './conversations/inbox-nav';

// Conversation lists: "Select" switches the list into selection mode (checkboxes and
// the action bar), "Select all" ticks every conversation on the page.
const form = document.querySelector('.js-inbox-form');

// The folder drop-down under the list title closes when clicking elsewhere.
document.addEventListener('click', (event) => {
    document.querySelectorAll('details.c-inbox-folders[open], details.c-inbox-more[open], details.c-inbox-head__menu[open]').forEach((menu) => {
        if (!menu.contains(event.target)) {
            menu.removeAttribute('open');
        }
    });
});

if (form) {
    const selectButton = form.querySelector('.js-inbox-select');
    const checkAllButton = form.querySelector('.js-inbox-check-all');
    const checkboxes = () => form.querySelectorAll('input[type=checkbox].checkable');

    const actionButtons = form.querySelectorAll('.c-inbox-actions__btn');

    // The actions stay greyed out until at least one conversation is ticked.
    const updateCheckAllLabel = () => {
        const boxes = Array.from(checkboxes());
        const anyChecked = boxes.some((box) => box.checked);
        actionButtons.forEach((button) => { button.disabled = !anyChecked; });
        if (!checkAllButton) {
            return;
        }
        const allChecked = boxes.length > 0 && boxes.every((box) => box.checked);
        checkAllButton.textContent = allChecked
            ? checkAllButton.dataset.labelUncheck
            : checkAllButton.dataset.labelCheck;
    };

    const setSelecting = (selecting) => {
        form.classList.toggle('is-selecting', selecting);
        if (selectButton) {
            const label = selecting ? selectButton.dataset.labelDone : selectButton.dataset.labelSelect;
            selectButton.title = label;
            selectButton.setAttribute('aria-label', label);
            selectButton.setAttribute('aria-pressed', selecting ? 'true' : 'false');
        }
        if (!selecting) {
            checkboxes().forEach((box) => { box.checked = false; });
        }
        updateCheckAllLabel();
    };

    if (selectButton) {
        selectButton.addEventListener('click', () => {
            setSelecting(!form.classList.contains('is-selecting'));
        });
    }

    if (checkAllButton) {
        checkAllButton.addEventListener('click', () => {
            const boxes = Array.from(checkboxes());
            const check = !boxes.every((box) => box.checked);
            boxes.forEach((box) => { box.checked = check; });
            updateCheckAllLabel();
        });
    }

    form.addEventListener('change', (event) => {
        if (event.target.matches('input.checkable')) {
            updateCheckAllLabel();
        }
    });

    // In selection mode a click on a conversation ticks it instead of opening it.
    form.addEventListener('click', (event) => {
        if (!form.classList.contains('is-selecting')) {
            return;
        }
        const link = event.target.closest('.c-inbox-item__link');
        if (!link) {
            return;
        }
        event.preventDefault();
        const box = link.closest('.c-inbox-item').querySelector('input.checkable');
        if (box) {
            box.checked = !box.checked;
            updateCheckAllLabel();
        }
    });

    updateCheckAllLabel();

    // Keep the selection mode on when the browser restores ticked boxes (back button).
    if (Array.from(checkboxes()).some((box) => box.checked)) {
        setSelecting(true);
    }
}

// Reply box under a conversation: grows with the text, and sends the text as paragraphs
// (messages are stored as HTML, like the ones written with the editor).
// Runs again when the inbox shows another conversation without reloading (inbox-nav.js).
function initReply() {
    const replyForm = document.querySelector('.js-inbox-reply');
    if (!replyForm || replyForm.dataset.replyReady) {
        return;
    }
    replyForm.dataset.replyReady = '1';
    const textarea = replyForm.querySelector('.js-inbox-reply-text');
    const button = replyForm.querySelector('button[type=submit]');

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

    const resize = () => {
        textarea.style.height = 'auto';
        textarea.style.height = Math.min(textarea.scrollHeight, 160) + 'px';
    };

    const update = () => {
        resize();
        button.disabled = textarea.value.trim() === '' && !hasPhotos(replyForm);
    };

    textarea.addEventListener('input', update);
    replyForm.addEventListener('photos:change', update);
    update();

    replyForm.addEventListener('submit', (event) => {
        if (textarea.value.trim() === '' && !hasPhotos(replyForm)) {
            event.preventDefault();
            return;
        }
        const html = document.createElement('input');
        html.type = 'hidden';
        html.name = textarea.name;
        html.value = toHtml(textarea.value) + photosHtml(replyForm);
        const name = textarea.name;
        textarea.removeAttribute('name');
        textarea.readOnly = true;
        replyForm.appendChild(html);
        button.disabled = true;
        // Text only: the bubble shows at once and the page stays (photos go the usual way)
        if (!hasPhotos(replyForm) && sendInPlace(replyForm, event.submitter, html.value, (sent) => {
            html.remove();
            textarea.name = name;
            textarea.readOnly = false;
            if (sent) {
                textarea.value = '';
            }
            update();
        })) {
            event.preventDefault();
        }
    });
}

initReply();
initMessagePhotos();
initBubblePhotos();

// Desktop: a conversation picked in the list loads in the pane, without reloading the page
initInboxNav(() => {
    initReply();
    initRequests();
});

// Phones: the conversation stays behind a loader until the page has finished setting it up
// (icons, buttons), so it doesn't show half built (the CSS shows it anyway after a moment)
const pane = document.querySelector('.c-inbox__pane');
const paneReady = () => requestAnimationFrame(() => requestAnimationFrame(() => pane.classList.add('is-ready')));
if (pane) {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', paneReady);
    } else {
        paneReady();
    }
}
