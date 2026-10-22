/*
 * "Contact a member" side panel: Send stays greyed, with a hint under it, until every
 * .js-contact-required field of the form is filled.
 */
export function initContactSendState() {
    document.querySelectorAll('.js-contact-form').forEach((form) => {
        const send = form.querySelector('.js-contact-send');
        const hint = form.querySelector('.js-contact-hint');
        const fields = form.querySelectorAll('.js-contact-required');
        if (!send) {
            return;
        }
        const refresh = () => {
            const ready = Array.from(fields).every((field) => field.value.trim() !== '');
            send.disabled = !ready;
            if (hint) {
                hint.hidden = ready;
            }
        };
        fields.forEach((field) => {
            field.addEventListener('input', refresh);
            field.addEventListener('change', refresh);
        });
        refresh();
    });
}
