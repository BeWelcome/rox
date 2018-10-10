require('ekko-lightbox');
require('ekko-lightbox/dist/ekko-lightbox.css');

$(function () {
$(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });
});
