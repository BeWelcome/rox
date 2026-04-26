import Sortable from 'sortablejs';

document.addEventListener('DOMContentLoaded', function () {
    const faqsContainer = document.getElementById('faqs');
    if (faqsContainer) {
        new Sortable(faqsContainer, {
            animation: 150,
            onUpdate: function (evt) {
                const itemEls = faqsContainer.querySelectorAll('.card');
                const data = Array.from(itemEls).map(el => 'faq[]=' + el.id.replace('faq_', '')).join('&');
                document.getElementById('form_sortOrder').value = data;
            }
        });
    }
});