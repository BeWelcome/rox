/*
 * The FAQ admin (templates/admin/faqs/): the FAQs of a category and the categories are sorted by dragging them by
 * their handle; the new order (ids, comma-separated) goes into the form, whose Save button is enabled then.
 */
import Sortable from 'sortablejs';

document.querySelectorAll('.js-faq-sort').forEach((form) => {
    const list = form.querySelector('.js-faq-sortable');
    const order = form.querySelector('.js-faq-order');
    const save = form.querySelector('.js-faq-save');
    if (!list || !order) {
        return;
    }
    const current = () => [...list.children].map((row) => row.dataset.id).join(',');
    const initial = current();

    Sortable.create(list, {
        animation: 150,
        handle: '.js-faq-handle',
        ghostClass: 'is-dragging',
        onUpdate: () => {
            order.value = current();
            if (save) {
                save.disabled = order.value === initial;
            }
        },
    });
});
