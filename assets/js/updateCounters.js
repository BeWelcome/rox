function updateCount() {
    $.ajax({
        type: 'POST',
        url: '/count/conversations/unread',
        dataType: 'json',
        success: function (data) {
            $('#conversationCount').replaceWith(data.html);
        }});
}

let interval = setInterval(function () { updateCount(); }, 600 * 1000);

updateCount();
