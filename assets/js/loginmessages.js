import 'jquery';

$('.close').on('click', function() {
    const id = $(this).data('alert');
    $.ajax({
        url: '/loginmessage/acknowledge/' + id,
        type: 'POST'
    });
});
