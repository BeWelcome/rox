/*
 * Sending a message without leaving the conversation: the bubble shows at once, pale, at the
 * bottom of the thread (#message_thread, written newest first), the form is posted in the
 * background, and the thread of the page that comes back replaces this one (the bubble turns
 * solid). Used by the reply box (conversations.js) and by a plain message in the answer to a
 * hosting request (requests.js). Returns false when it can't (no thread, no fetch): the form
 * is then sent the usual way.
 */
export function sendInPlace(form, submitter, html, done) {
    const thread = document.getElementById('message_thread');
    if (!thread || !window.fetch || !window.DOMParser) {
        return false;
    }
    const body = new FormData(form);
    if (submitter && submitter.name) {
        body.append(submitter.name, submitter.value || '');
    }

    const row = document.createElement('div');
    row.className = 'c-bubble-row is-own is-pending';
    const now = new Date();
    row.innerHTML = '<div class="c-bubble-stack is-own"><div class="c-bubble c-bubble--own">'
        + '<div class="c-bubble__text"></div>'
        + '<time class="c-bubble__time"></time></div></div>';
    row.querySelector('.c-bubble__text').innerHTML = html;
    row.querySelector('time').textContent = now.toLocaleTimeString(document.documentElement.lang || undefined, { hour: 'numeric', minute: '2-digit' });
    thread.prepend(row);
    // column-reverse: the bottom of the thread is scrollTop 0
    thread.scrollTop = 0;

    // disableButtonOnSubmit() (submit_button_disable.js) greys every button on submit: the page
    // stays here, so they come back once it is sent
    const finish = (sent) => {
        document.querySelectorAll('button.disabled.u\\:pointer-events-none').forEach((button) => {
            button.classList.remove('disabled', 'u:pointer-events-none');
        });
        done(sent);
    };

    fetch(form.action || window.location.href, { method: 'POST', body, credentials: 'same-origin' })
        .then((response) => response.text().then((text) => ({ response, text })))
        .then(({ response, text }) => {
            const page = new DOMParser().parseFromString(text, 'text/html');
            const fresh = page.getElementById('message_thread');
            if (response.ok && fresh) {
                row.classList.remove('is-pending');
                // A short moment to see it turn solid, then the real thread (with its time and date)
                setTimeout(() => {
                    thread.innerHTML = fresh.innerHTML;
                    thread.scrollTop = 0;
                }, 250);
                finish(true);
            } else if (response.ok) {
                // Something else came back (another page): go there
                window.location.assign(response.url);
            } else {
                row.classList.remove('is-pending');
                row.classList.add('is-failed');
                finish(false);
            }
        })
        .catch(() => {
            row.classList.remove('is-pending');
            row.classList.add('is-failed');
            finish(false);
        });
    return true;
}
