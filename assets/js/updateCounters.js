function updateCounts() {
    $.ajax({
        type: 'POST',
        url: '/count/messages/unread',
        dataType: 'json',
        success: function (data) {
            $('#messageCount').replaceWith(data.html);
        }});
    $.ajax({
        type: 'POST',
        url: '/count/requests/unread',
        dataType: 'json',
        success: function (data) {
            $('#requestCount').replaceWith(data.html);
        }});
    $.ajax({
        type: 'POST',
        url: '/count/invitations/unread',
        dataType: 'json',
        success: function (data) {
            $('#invitationCount').replaceWith(data.html);
        }});
}

let interval = setInterval(function () { updateCounts(); }, 600 * 1000);

updateCounts();
