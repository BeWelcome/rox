/*
 * The rights and flags pages (templates/admin/rights, templates/admin/flags):
 * - the tabs scroll sideways on phones;
 * - a remove button (data-remove: the dialog's id) fills the remove dialog with its URL and question, then opens it;
 * - the filters (data-autosubmit) apply when a select or checkbox changes;
 * - the assign form shows the chosen right's description under it;
 * - the member field of the assign form suggests members (member/suggest).
 */
import MicroModal from 'micromodal';
import {initTabsScrollers} from '../tabs-scroller';
import {initMemberSuggestFields} from '../member/suggest';

initTabsScrollers();
initMemberSuggestFields();
MicroModal.init({ awaitCloseAnimation: true });

document.querySelectorAll('[data-remove]').forEach((button) => {
    button.addEventListener('click', () => {
        const dialog = document.getElementById(button.dataset.remove);
        if (!dialog) {
            return;
        }
        dialog.querySelector('[data-remove-form]').action = button.dataset.action;
        dialog.querySelector('[data-remove-text]').textContent = button.dataset.text;
        MicroModal.show(dialog.id, { awaitCloseAnimation: true });
    });
});

document.querySelectorAll('form[data-autosubmit]').forEach((form) => {
    form.querySelectorAll('select, input[type=checkbox]').forEach((field) => {
        field.addEventListener('change', () => form.requestSubmit());
    });
});

const select = document.getElementById('right_assignment_right');
const hint = document.querySelector('[data-right-hint]');
if (select && hint) {
    select.addEventListener('change', () => {
        const option = select.options[select.selectedIndex];
        hint.hidden = !select.value;
        hint.querySelector('[data-right-hint-name]').textContent = select.value ? option.text : '';
        hint.querySelector('[data-right-hint-text]').textContent = option.dataset.description ?? '';
    });
}
