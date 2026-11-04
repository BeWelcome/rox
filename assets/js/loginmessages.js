/*
 * Site announcements (templates/layout/_login_messages.html.twig): "Got it" acknowledges the message. Its strip folds
 * away at once; if the request fails it comes back with an error toast.
 */
const container = document.querySelector('[data-login-messages]');

function showError(text) {
    const toasts = document.getElementById('toasts');
    if (!toasts) {
        return;
    }
    const toast = document.createElement('div');
    toast.className = 'bw-toast bw-toast--error';
    toast.setAttribute('role', 'alert');
    const icon = document.createElement('div');
    icon.className = 'bw-toast__icon';
    icon.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
    const body = document.createElement('div');
    body.className = 'bw-toast__body';
    body.textContent = text;
    toast.append(icon, body);
    toasts.append(toast);
}

if (container) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    container.querySelectorAll('[data-login-message]').forEach((strip) => {
        const button = strip.querySelector('[data-login-message-ack]');
        button.addEventListener('click', () => {
            const body = new FormData();
            body.append('_token', container.dataset.token);
            const sent = fetch(strip.dataset.loginMessage, { method: 'POST', body, credentials: 'same-origin' })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(String(response.status));
                    }
                });

            // fold the strip away without waiting, and remove it once folded
            button.disabled = true;
            strip.classList.add('is-leaving');
            const removed = new Promise((resolve) => {
                if (reducedMotion) {
                    resolve();
                } else {
                    strip.addEventListener('transitionend', (event) => {
                        if ('grid-template-rows' === event.propertyName) {
                            resolve();
                        }
                    });
                    // in case the transition doesn't run
                    window.setTimeout(resolve, 600);
                }
            });

            Promise.all([sent, removed])
                .then(() => {
                    strip.remove();
                    if (null === container.querySelector('[data-login-message]')) {
                        container.remove();
                    }
                })
                .catch(() => {
                    strip.classList.remove('is-leaving');
                    button.disabled = false;
                    showError(container.dataset.error);
                });
        });
    });
}
