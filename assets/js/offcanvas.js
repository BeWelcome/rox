document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    document.querySelectorAll('[data-bs-toggle="offcanvas"]').forEach(function(element) {
        element.addEventListener('click', function () {
            document.querySelector('.offcanvas-collapse').classList.toggle('open');
        });
    });
});
