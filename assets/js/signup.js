$(function () {
    $('[data-toggle="popover"]').popover({ html : true });

    $("#mothertongue").select2({
        theme: 'bootstrap',
        containerCssClass: 'form-control',
        placeholder: 'Select a language',
        allowClear: true
    });
});
