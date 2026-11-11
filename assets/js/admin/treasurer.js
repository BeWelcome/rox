/*
 * The donation form of the treasurer (templates/admin/treasurer/edit_donation.html.twig): "Who donated?" shows the
 * donor's username (a member, with suggestions), their name (not a member) or neither (unknown). The form clears the
 * hidden field on submit (DonationType), so a value left in it isn't saved.
 */
import {initMemberSuggestFields} from '../member/suggest';

initMemberSuggestFields();

document.querySelectorAll('.js-treasurer-donation').forEach((form) => {
    const types = form.querySelectorAll('.js-donor-type');
    const show = () => {
        const checked = form.querySelector('.js-donor-type:checked');
        const type = checked ? checked.value : '';
        form.querySelectorAll('[data-donor-type]').forEach((field) => {
            field.hidden = field.dataset.donorType !== type;
        });
    };
    types.forEach((radio) => radio.addEventListener('change', show));
    show();
});
