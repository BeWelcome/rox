import 'jquery';

$('.close').on('click', function() {
    const id = $(this).data('alert');
    $.ajax({
        url: '/close/' + id,
        type: 'POST'
    });
});
