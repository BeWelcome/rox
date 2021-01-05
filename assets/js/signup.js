$(function () {
    $('[data-toggle="popover"]').popover({ html : true });

    $("#mothertongue").select2({
        theme: 'bootstrap4',
        placeholder: 'Select a language',
        allowClear: true,
        width: 'auto'
    });
});
