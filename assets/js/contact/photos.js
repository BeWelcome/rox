import {openPhotoViewer} from '../photo-viewer';

/*
 * Photos in messages, hosting requests and invitations. The camera buttons
 * (conversations/_photo_attach.html.twig) pick pictures, which are made smaller in the
 * browser and uploaded right away (the forum editor's upload, /gallery/upload/image). On
 * send, the form's text field adds them to the message as images (photosHtml), since
 * messages are stored as HTML. In the conversation (conversations/_thread.html.twig), a
 * click on a picture opens it large.
 */
const MAX_SIDE = 1600;

const escapeAttribute = (value) => value
    .replace(/&/g, '&amp;')
    .replace(/"/g, '&quot;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;');

/* A smaller JPEG for big pictures; the file itself when the browser can't read it (HEIC...). */
async function shrink(file) {
    if (file.type === 'image/gif' || !('createImageBitmap' in window)) {
        return file;
    }
    try {
        const bitmap = await createImageBitmap(file);
        const scale = Math.min(1, MAX_SIDE / Math.max(bitmap.width, bitmap.height));
        if (scale === 1 && file.size < 1500000) {
            bitmap.close();
            return file;
        }
        const canvas = document.createElement('canvas');
        canvas.width = Math.round(bitmap.width * scale);
        canvas.height = Math.round(bitmap.height * scale);
        canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
        bitmap.close();
        const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.85));
        if (!blob) {
            return file;
        }
        return new File([blob], file.name.replace(/\.[^.]+$/, '') + '.jpg', { type: 'image/jpeg' });
    } catch (error) {
        return file;
    }
}

const items = (form) => form.querySelectorAll('.js-photo-strip .js-photo-item');

export function hasPhotos(form) {
    return Array.from(items(form)).some((item) => item.dataset.url);
}

/* The uploaded pictures as HTML, added after the text of the message. */
export function photosHtml(form) {
    return Array.from(items(form))
        .filter((item) => item.dataset.url)
        .map((item) => '<p><img src="' + escapeAttribute(item.dataset.url) + '" alt=""></p>')
        .join('');
}

const fill = (template, count) => (template || '').replace('{count}', count);

function refresh(form) {
    const input = form.querySelector('.js-photo-input');
    const max = parseInt(input.dataset.max, 10) || 5;
    const count = items(form).length;
    const full = count >= max;

    form.querySelectorAll('.js-photo-tray').forEach((tray) => { tray.hidden = count === 0; });
    form.querySelectorAll('.js-photo-empty').forEach((element) => { element.hidden = count > 0; });
    form.querySelectorAll('.js-photo-full').forEach((element) => { element.hidden = count === 0; });
    form.querySelectorAll('.js-photo-count').forEach((element) => { element.textContent = count; });
    form.querySelectorAll('.js-photo-ready').forEach((element) => {
        element.textContent = fill(count === 1 ? element.dataset.one : element.dataset.many, count);
    });
    form.querySelectorAll('.js-photo-attach').forEach((button) => { button.disabled = full; });
    form.querySelectorAll('.c-photo-strip__add').forEach((button) => { button.hidden = full; });
    // "Send with 2 photos" (new message)
    form.querySelectorAll('.js-photo-send-label').forEach((label) => {
        if (label.dataset.default === undefined) {
            label.dataset.default = label.textContent;
        }
        label.textContent = count === 0 ? label.dataset.default : fill(count === 1 ? label.dataset.one : label.dataset.many, count);
    });
    form.classList.toggle('has-photos', count > 0);
    form.dispatchEvent(new CustomEvent('photos:change'));
}

function addItem(form, src, labels) {
    const strip = form.querySelector('.js-photo-strip');
    const add = strip.querySelector('.c-photo-strip__add');
    const item = document.createElement('div');
    item.className = 'c-photo-strip__item js-photo-item';
    item.innerHTML = `
        <img class="c-photo-strip__img" src="${escapeAttribute(src)}" alt="">
        <span class="c-photo-strip__spinner" aria-hidden="true"></span>
        <button type="button" class="c-photo-strip__remove" aria-label="${escapeAttribute(labels.remove)}">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>`;
    item.querySelector('.c-photo-strip__remove').addEventListener('click', () => {
        item.remove();
        refresh(form);
    });
    strip.insertBefore(item, add);
    refresh(form);

    return item;
}

async function upload(form, file, input) {
    const labels = { remove: input.dataset.labelRemove || 'Remove', error: input.dataset.labelError || 'Error' };
    const preview = URL.createObjectURL(file);
    const item = addItem(form, preview, labels);
    item.classList.add('is-uploading');

    try {
        const body = new FormData();
        body.append('upload', await shrink(file));
        const response = await fetch(input.dataset.uploadUrl, { method: 'POST', body, credentials: 'same-origin' });
        const result = await response.json();
        if (!result.uploaded) {
            throw new Error(result.error && result.error.message ? result.error.message : labels.error);
        }
        item.dataset.url = result.url;
        item.classList.remove('is-uploading');
    } catch (error) {
        item.classList.remove('is-uploading');
        item.classList.add('has-error');
        item.setAttribute('aria-label', error.message || labels.error);
        setTimeout(() => {
            item.remove();
            refresh(form);
        }, 4000);
    }
    refresh(form);
}

export function initMessagePhotos() {
    document.querySelectorAll('.js-photo-input').forEach((input) => {
        const form = input.closest('form');
        if (!form || form.dataset.photosReady) {
            return;
        }
        form.dataset.photosReady = '1';
        const max = parseInt(input.dataset.max, 10) || 5;

        form.querySelectorAll('.js-photo-attach').forEach((button) => {
            button.addEventListener('click', () => input.click());
        });
        input.addEventListener('change', () => {
            const room = max - items(form).length;
            Array.from(input.files).slice(0, Math.max(0, room)).forEach((file) => upload(form, file, input));
            input.value = '';
        });

        // Back after a form error: the pictures that were sent are in the field's HTML.
        const field = form.querySelector('.js-plain-message[data-html]');
        if (field && field.dataset.html) {
            const doc = new DOMParser().parseFromString(field.dataset.html, 'text/html');
            doc.querySelectorAll('img[src*="/gallery/show/uploaded/"]').forEach((image) => {
                const item = addItem(form, image.getAttribute('src'), { remove: input.dataset.labelRemove || 'Remove' });
                item.dataset.url = image.getAttribute('src');
            });
        }
        refresh(form);

        // Not while a picture is still uploading (capture: before the text fields' own handler).
        form.addEventListener('submit', (event) => {
            if (form.querySelector('.js-photo-item.is-uploading')) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
    });
}

/*
 * The conversation's photo viewer: black, the photo in the middle, who sent it and when at the
 * top with Download and Close, the message's text at the bottom. Arrows, the keyboard and a
 * swipe go through the conversation's photos.
 */
let chatViewer = null;

const decode = (text) => new DOMParser().parseFromString(text || '', 'text/html').documentElement.textContent;

function when(iso) {
    const date = new Date(iso);
    if (Number.isNaN(date.getTime())) {
        return '';
    }
    const lang = document.documentElement.lang || undefined;
    const time = date.toLocaleTimeString(lang, { hour: 'numeric', minute: '2-digit' });
    const days = Math.round((new Date().setHours(0, 0, 0, 0) - new Date(date).setHours(0, 0, 0, 0)) / 86400000);
    if (days >= 0 && days < 2 && 'RelativeTimeFormat' in Intl) {
        const day = new Intl.RelativeTimeFormat(lang, { numeric: 'auto' }).format(-days, 'day');
        return day.charAt(0).toUpperCase() + day.slice(1) + ', ' + time;
    }
    return date.toLocaleDateString(lang, { day: 'numeric', month: 'short', year: 'numeric' }) + ', ' + time;
}

function buildChatViewer(labels) {
    chatViewer = document.createElement('div');
    chatViewer.className = 'c-chat-viewer';
    chatViewer.hidden = true;
    chatViewer.setAttribute('role', 'dialog');
    chatViewer.setAttribute('aria-modal', 'true');
    chatViewer.innerHTML = `
        <div class="c-chat-viewer__top">
            <img class="c-chat-viewer__avatar js-cv-avatar" alt="">
            <div class="c-chat-viewer__who">
                <strong class="js-cv-author"></strong>
                <span class="js-cv-when"></span>
            </div>
            <a class="c-chat-viewer__btn js-cv-download" download aria-label="${escapeAttribute(labels.download)}"><i class="fas fa-download" aria-hidden="true"></i></a>
            <button type="button" class="c-chat-viewer__btn js-cv-close" aria-label="${escapeAttribute(labels.close)}"><i class="fas fa-times" aria-hidden="true"></i></button>
        </div>
        <div class="c-chat-viewer__stage js-cv-stage">
            <button type="button" class="c-chat-viewer__nav c-chat-viewer__nav--prev js-cv-prev" aria-label="Previous"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
            <img class="c-chat-viewer__img js-cv-img" alt="">
            <button type="button" class="c-chat-viewer__nav c-chat-viewer__nav--next js-cv-next" aria-label="Next"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
        </div>
        <p class="c-chat-viewer__caption js-cv-caption"></p>
    `;
    document.body.appendChild(chatViewer);

    const close = () => {
        chatViewer.hidden = true;
        document.documentElement.classList.remove('has-chat-viewer');
    };
    chatViewer.close = close;
    chatViewer.querySelector('.js-cv-close').addEventListener('click', close);
    chatViewer.querySelector('.js-cv-prev').addEventListener('click', () => showChatPhoto(chatViewer.index - 1));
    chatViewer.querySelector('.js-cv-next').addEventListener('click', () => showChatPhoto(chatViewer.index + 1));
    chatViewer.querySelector('.js-cv-stage').addEventListener('click', (event) => {
        if (event.target.classList.contains('js-cv-stage')) {
            close();
        }
    });
    window.addEventListener('keydown', (event) => {
        if (chatViewer.hidden) {
            return;
        }
        if (event.key === 'Escape') {
            event.stopImmediatePropagation();
            close();
        } else if (event.key === 'ArrowLeft') {
            showChatPhoto(chatViewer.index - 1);
        } else if (event.key === 'ArrowRight') {
            showChatPhoto(chatViewer.index + 1);
        }
    }, true);
    let startX = null;
    chatViewer.addEventListener('touchstart', (event) => { startX = event.touches[0].clientX; }, { passive: true });
    chatViewer.addEventListener('touchend', (event) => {
        if (startX === null) {
            return;
        }
        const dx = event.changedTouches[0].clientX - startX;
        startX = null;
        if (Math.abs(dx) > 50) {
            showChatPhoto(chatViewer.index + (dx < 0 ? 1 : -1));
        }
    });
}

function showChatPhoto(index) {
    const photos = chatViewer.photos;
    if (index < 0 || index >= photos.length) {
        return;
    }
    chatViewer.index = index;
    const photo = photos[index];
    const $ = (selector) => chatViewer.querySelector(selector);
    $('.js-cv-img').src = photo.src;
    $('.js-cv-avatar').src = photo.avatar;
    $('.js-cv-author').textContent = photo.author;
    $('.js-cv-when').textContent = when(photo.when);
    $('.js-cv-download').href = photo.src;
    $('.js-cv-caption').textContent = photo.caption;
    $('.js-cv-caption').hidden = photo.caption === '';
    $('.js-cv-prev').hidden = index === 0;
    $('.js-cv-next').hidden = index === photos.length - 1;
}

function openChatViewer(photos, index, labels) {
    if (!chatViewer) {
        buildChatViewer(labels);
    }
    chatViewer.photos = photos;
    chatViewer.hidden = false;
    document.documentElement.classList.add('has-chat-viewer');
    showChatPhoto(index);
    chatViewer.querySelector('.js-cv-close').focus();
}

/* A click on a photo in a conversation opens the viewer on the conversation's photos. */
export function initBubblePhotos() {
    if (window.bwBubblePhotos) {
        return;
    }
    window.bwBubblePhotos = true;
    document.addEventListener('click', (event) => {
        const item = event.target.closest('.c-bubble-photos__item');
        if (item) {
            // The thread is written newest first (shown in reverse): the photos in reading order.
            const rows = Array.from(document.querySelectorAll('.c-bubble-row')).reverse();
            const items = rows.flatMap((row) => Array.from(row.querySelectorAll('.c-bubble-photos__item')));
            const photos = items.map((element) => {
                const group = element.closest('.c-bubble-photos');
                const image = element.querySelector('img');
                return {
                    src: image.currentSrc || image.src,
                    author: group.dataset.author || '',
                    avatar: group.dataset.avatar || '',
                    when: group.dataset.when || '',
                    caption: decode(group.dataset.caption).trim(),
                };
            });
            const thread = item.closest('.c-inbox-thread');
            openChatViewer(photos, Math.max(0, items.indexOf(item)), {
                download: (thread && thread.dataset.labelDownload) || 'Download',
                close: (thread && thread.dataset.labelClose) || 'Close',
            });
            return;
        }

        // Pictures inside a text (the old editor's): the site's photo viewer
        const image = event.target.closest('.c-bubble__text img');
        if (!image || image.closest('a')) {
            return;
        }
        const all = Array.from(document.querySelectorAll('.c-bubble__text img')).filter((img) => !img.closest('a')).reverse();
        openPhotoViewer(all.map((img) => ({ src: img.currentSrc || img.src, title: '' })), Math.max(0, all.indexOf(image)));
    });
}
