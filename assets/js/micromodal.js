import MicroModal from 'micromodal';

MicroModal.init({
    awaitCloseAnimation: true
});

// A modal that the page wants open when it loads (e.g. the hint of the feedback page for a report)
document.querySelectorAll('.modal[data-micromodal-open]').forEach((modal) => {
    MicroModal.show(modal.id, { awaitCloseAnimation: true });
});
