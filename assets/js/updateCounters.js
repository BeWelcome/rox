function updateCounts() {
    let messageCount = $('#messageCount');
    let mcount = messageCount.data('count');
    $.ajax({
        type: 'POST',
        url: '/count/messages/unread',
        data: {
            current: mcount
        },
        dataType: 'json',
        success: function (data) {
            if (data.oldCount !== data.newCount) {
                $('#messageCount').replaceWith(data.html);
                $('#toasts').append(data.toast);
                $('[data-toggle="toast"]').toast('show');
            } else {
            }
        }});
    let requestCount = $('#requestCount');
    let rcount = requestCount.data('count');
    $.ajax({
        type: 'POST',
        url: '/count/requests/unread',
        data: {
            current: rcount
        },
        dataType: 'json',
        success: function (data) {
            if (data.oldCount !== data.newCount) {
                $('#requestCount').replaceWith(data.html);
                $('#toasts').append(data.toast);
                $('[data-toggle="toast"]').toast('show');
            } else {
            }
        }});
}

let interval = setInterval(function () { updateCounts(); }, 60 * 1000);

$(function () {
    $('[data-toggle="toast"]').toast({
        autohide: false
    })
});

