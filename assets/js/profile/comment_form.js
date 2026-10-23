import MicroModal from 'micromodal';

// Enable the submit button only once every required field is filled in.
const form = document.querySelector('[data-comment-form]');

if (form) {
    const submit = form.querySelector('.js-submit');
    const quality = form.querySelector('[name$="[quality]"]');
    const relations = form.querySelectorAll('[name$="[relations][]"]');
    const comment = form.querySelector('[name$="[comment]"]');
    const guidelines = form.querySelector('[name$="[guidelines]"]');

    const isComplete = () =>
        quality !== null && quality.value !== ''
        && Array.from(relations).some(relation => relation.checked)
        && comment !== null && comment.value.trim() !== ''
        && (guidelines === null || guidelines.checked);

    const update = () => {
        submit.disabled = !isComplete();
    };

    form.addEventListener('input', update);
    form.addEventListener('change', update);
    update();
}

// After an edit, ask whether the change is about a new experience.
if (document.querySelector('#experiences[data-open-on-load]')) {
    MicroModal.show('experiences', {awaitCloseAnimation: true});
}
