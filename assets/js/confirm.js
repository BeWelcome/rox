// Asks before a button or link with data-confirm does its work, in a small dialog
// (.c-confirm-modal) instead of the browser's confirm(). On the button:
//   data-confirm        the question
//   data-confirm-ok     label of the button that goes ahead (default: the button's own text)
//   data-confirm-close  label of the button that closes the dialog
// A submit button submits its form as if it had been clicked, so its name is sent.

let modal = null;

function build() {
    modal = document.createElement('div');
    modal.className = 'c-confirm-modal';
    modal.setAttribute('role', 'dialog');
    modal.setAttribute('aria-modal', 'true');
    modal.innerHTML = `
        <div class="c-confirm-modal__backdrop" data-confirm-dismiss></div>
        <div class="c-confirm-modal__dialog">
            <p class="c-confirm-modal__text"></p>
            <div class="c-confirm-modal__actions">
                <button type="button" class="c-confirm-modal__btn c-confirm-modal__btn--cancel" data-confirm-dismiss></button>
                <button type="button" class="c-confirm-modal__btn c-confirm-modal__btn--danger js-confirm-ok"></button>
            </div>
        </div>`;
    document.body.appendChild(modal);
    modal.addEventListener('click', (event) => {
        if (event.target.closest('[data-confirm-dismiss]')) {
            close();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('c-confirm-modal--open')) {
            close();
        }
    });
}

let opener = null;

function close() {
    modal.classList.remove('c-confirm-modal--open');
    if (opener) {
        opener.focus();
        opener = null;
    }
}

function open(trigger) {
    if (!modal) {
        build();
    }
    opener = trigger;
    modal.querySelector('.c-confirm-modal__text').textContent = trigger.dataset.confirm;
    modal.querySelector('[data-confirm-dismiss].c-confirm-modal__btn').textContent = trigger.dataset.confirmClose || 'Close';
    const ok = modal.querySelector('.js-confirm-ok');
    ok.textContent = trigger.dataset.confirmOk || trigger.textContent.trim();
    ok.onclick = () => {
        opener = null;
        close();
        trigger.dataset.confirmed = '1';
        if (trigger.form && typeof trigger.form.requestSubmit === 'function') {
            trigger.form.requestSubmit(trigger);
        } else {
            trigger.click();
        }
    };
    modal.classList.add('c-confirm-modal--open');
    ok.focus();
}

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm]');
    if (!trigger) {
        return;
    }
    if (trigger.dataset.confirmed === '1') {
        delete trigger.dataset.confirmed;
        return;
    }
    event.preventDefault();
    open(trigger);
});
