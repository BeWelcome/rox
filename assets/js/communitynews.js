/*
 * Community news (templates/communitynews, templates/admin/communitynews, templates/admin/checker/communitynews):
 * - a button waits for its form's text (data-needs-text, data-needs-text-button);
 * - Reply moves the one reply box under the comment answered;
 * - Report fills the report dialog with the comment and opens it; Report waits for a reason, and a note for "other";
 * - the editor's Preview as member shows the unsaved title and text in the article card;
 * - the checker's Select all and Delete selected.
 */
import MicroModal from 'micromodal';

MicroModal.init({ awaitCloseAnimation: true });

function watchText(form) {
    const button = form.querySelector('[data-needs-text-button]');
    const text = form.querySelector('[data-needs-text]');
    if (!button || !text) {
        return;
    }
    const update = () => { button.disabled = '' === text.value.trim(); };
    text.addEventListener('input', update);
    update();
}
document.querySelectorAll('form').forEach(watchText);

// Reply: one box, under the comment answered
const replyForm = document.querySelector('[data-reply-form]');
if (replyForm) {
    document.querySelectorAll('[data-reply-to]').forEach((button) => {
        button.addEventListener('click', () => {
            const comment = button.closest('.c-cnews-comment');
            replyForm.querySelector('[data-reply-parent]').value = button.dataset.replyTo;
            replyForm.querySelector('[data-reply-name]').textContent = button.dataset.replyName;
            comment.after(replyForm);
            replyForm.className = 'c-cnews-reply c-cnews-comment--indent-' + Math.min(2, (parseInt(comment.className.match(/indent-(\d)/)?.[1] ?? '0', 10) + 1));
            replyForm.hidden = false;
            replyForm.querySelector('textarea').focus();
        });
    });
    replyForm.querySelector('[data-reply-cancel]').addEventListener('click', () => {
        replyForm.hidden = true;
        replyForm.querySelector('textarea').value = '';
        replyForm.querySelector('[data-needs-text-button]').disabled = true;
    });
}

// Report: the dialog for the comment of the button
const reportForm = document.querySelector('[data-report-form]');
if (reportForm) {
    const note = reportForm.querySelector('[data-report-note]');
    const noteLabel = reportForm.querySelector('[data-report-note-label]');
    const submit = reportForm.querySelector('[data-report-submit]');
    const update = () => {
        const reason = reportForm.querySelector('[data-report-reason]:checked');
        const other = reason !== null && 'other' === reason.value;
        noteLabel.textContent = other ? noteLabel.dataset.required : noteLabel.dataset.optional;
        note.required = other;
        submit.disabled = null === reason || (other && '' === note.value.trim());
    };
    reportForm.addEventListener('change', update);
    note.addEventListener('input', update);
    document.querySelectorAll('[data-report]').forEach((button) => {
        button.addEventListener('click', () => {
            reportForm.reset();
            reportForm.action = button.dataset.report;
            reportForm.querySelector('[data-report-name]').textContent = button.dataset.reportName;
            reportForm.querySelector('[data-report-text]').textContent = button.dataset.reportText;
            reportForm.querySelector('[data-report-avatar]').src = button.dataset.reportAvatar;
            update();
            MicroModal.show('c-cnews-report', { awaitCloseAnimation: true });
        });
    });
}

// The editor: Edit or Preview as member, the visibility hint, Save waits for a title
const editor = document.querySelector('[data-news-editor]');
if (editor) {
    const title = editor.querySelector('[data-news-title]');
    const save = editor.querySelector('[data-news-save]');
    const updateSave = () => { save.disabled = '' === title.value.trim(); };
    title.addEventListener('input', updateSave);
    updateSave();

    const visible = editor.querySelector('[data-news-public]');
    const hint = editor.querySelector('[data-news-public-hint]');
    visible.addEventListener('change', () => { hint.textContent = visible.checked ? hint.dataset.on : hint.dataset.off; });

    document.querySelectorAll('[data-mode]').forEach((tab) => {
        tab.addEventListener('click', () => {
            const preview = 'preview' === tab.dataset.mode;
            document.querySelectorAll('[data-mode]').forEach((other) => {
                other.classList.toggle('is-active', other === tab);
                other.setAttribute('aria-selected', other === tab ? 'true' : 'false');
            });
            editor.querySelectorAll('[data-mode-pane]').forEach((pane) => { pane.hidden = pane.dataset.modePane !== tab.dataset.mode; });
            if (preview) {
                const editable = editor.querySelector('.ck-editor__editable');
                editor.querySelector('[data-preview-title]').textContent = title.value;
                // the editor's own HTML, as the admin wrote it; saved, it is purified for the members
                editor.querySelector('[data-preview-text]').innerHTML = editable?.ckeditorInstance
                    ? editable.ckeditorInstance.getData()
                    : editor.querySelector('textarea').value;
            }
            window.scrollTo({ top: 0 });
        });
    });
}

// The checker: Select all, Delete selected
const selectForm = document.querySelector('[data-select-form]');
if (selectForm) {
    const all = selectForm.querySelector('[data-select-all]');
    const label = selectForm.querySelector('[data-select-label]');
    const submit = selectForm.querySelector('[data-select-submit]');
    const boxes = [...selectForm.querySelectorAll('[data-select-one]')];
    const update = () => {
        const selected = boxes.filter((box) => box.checked).length;
        all.checked = selected === boxes.length;
        all.indeterminate = 0 < selected && selected < boxes.length;
        label.textContent = 0 === selected ? label.dataset.none : label.dataset.some.replace('#', selected);
        submit.disabled = 0 === selected;
    };
    all.addEventListener('change', () => { boxes.forEach((box) => { box.checked = all.checked; }); update(); });
    boxes.forEach((box) => box.addEventListener('change', update));
    update();
}
