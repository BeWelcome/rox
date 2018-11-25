$(document).ready(function () {
    'use strict';

    $('[data-toggle="offcanvas"]').on('click', function () {
        $('.offcanvas-collapse').toggleClass('open');
    });
    $('[data-toggle="offcanvas1"]').on('click', function () {
        $('.offcanvas1-collapse').toggleClass('open');
    });
});