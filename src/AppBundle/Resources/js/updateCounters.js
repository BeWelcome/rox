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
            if (data.oldCount != data.newCount) {
                $('#messageCount').replaceWith(data.html);
                $('#messageCount').tooltip('show');
            } else {
                $('#messageCount').tooltip('hide');
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
            if (data.oldCount != data.newCount) {
                $('#requestCount').replaceWith(data.html);
                $('#requestCount').tooltip('show');
            } else {
                $('#requestCount').tooltip('hide');
            }
        }});
}

let interval = setInterval(function () { updateCounts(); }, 120 * 1000);

$(function () {
    $('[data-toggle="tooltip"]').tooltip()
})
