function updateCount() {
    $.ajax({
        type: 'POST',
        url: '/count/conversations/unread',
        dataType: 'json',
        success: function (data) {
            $('#conversationCount').replaceWith(data.html);
            if (typeof autocollapse_menu === "function") {
                autocollapse_menu(true);
            }
        }});
}

let interval = setInterval(function () { updateCount(); }, 60000 * 1000);

updateCount();
