/*
 * Contact us (templates/about/feedback.html.twig): choosing Safety and abuse shows the note under the topic, which
 * points to reporting from the member's profile or the conversation.
 */
document.querySelectorAll('.js-contact-topic[data-safety]').forEach((topic) => {
    const note = document.getElementById(topic.getAttribute('aria-describedby'));
    if (!note || '' === topic.dataset.safety) {
        return;
    }
    const show = () => {
        note.hidden = topic.value !== topic.dataset.safety;
    };
    topic.addEventListener('change', show);
    show();
});
