import MicroModal from 'micromodal';

// Save stays pale while something is missing, and says what (data-cform-required holds
// the words): how you know them, how it went, the text, the guidelines.
const form = document.querySelector('[data-comment-form]');

if (form) {
    const submit = form.querySelector('.js-submit');
    const saveContent = submit.innerHTML;
    const groups = Array.from(form.querySelectorAll('[data-cform-required]'));

    const isFilled = (group) => {
        const text = group.querySelector('textarea');
        if (text) {
            return text.value.trim() !== '';
        }
        return group.querySelector('input:checked') !== null;
    };

    const update = () => {
        const missing = groups.filter(group => !isFilled(group));
        submit.disabled = missing.length > 0;
        if (missing.length > 0) {
            submit.textContent = missing[0].dataset.cformRequired;
        } else {
            submit.innerHTML = saveContent;
        }
    };

    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
}

// After an edit, ask whether the change is about a new experience.
if (document.querySelector('#experiences[data-open-on-load]')) {
    MicroModal.show('experiences', {awaitCloseAnimation: true});
}
