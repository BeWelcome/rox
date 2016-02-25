$(document).ready(function() {
    updateMessages();
    updateThreads();

    $('a[data-toggle="tab"]').on('show.bs.tab', function (e) {
        switch($(e.target).attr('aria-controls')) {
            case 'messages':
                updateMessages();
                break;
            case 'notifications':
                updateNotifications();
                break;
            case 'threads':
                updateThreads();
                break;
            case 'activities':
                updateActivities();
                break;
        }
    });

    $("#all, #unread").change( function() {
        setTimeout(updateMessages, 500);
    });

    $("#groups, #forum, #following").change( function() {
        setTimeout(updateThreads, 500);
    });
});

function updateMessages() {
    var all = $('#all').hasClass('active') ? 1 : 0;
    var unread = $('#unread').hasClass('active') ? 1 : 0;
    $.ajax({
        type: "GET",
        url: '/widget/messages',
        data: {
            all: all,
            unread: unread
        },
        success: function(messages) {
            $("#messagesdisplay").replaceWith(messages);
        }
    });
}

function updateNotifications() {
    $.ajax({
        type: "GET",
        url: '/widget/notifications',
        success: function(notifications){
            $("#notificationsdisplay").replaceWith(notifications);
            // Set click event
            $('.notify').click(function () {
                var that = $(this);
                var id = $(this).attr('id');
                $.ajax({
                    type: "GET",
                    url: '/notify/' + id.replace('notify-','') + '/check',
                    success: function(data){
                        // update the notifications
                        updateNotifications();
                    }
                });
            })
        }
    });
}

function updateThreads() {
    // Get parameters
    var groups = $('#groups').hasClass('active') ? 1 : 0;
    var forum = $('#forum').hasClass('active') ? 1 : 0;
    var following = $('#following').hasClass('active') ? 1 : 0;
    $.ajax({
        type: "GET",
        url: '/widget/threads',
        data: {
            groups: groups,
            forum: forum,
            following: following
        },
        success: function(threads){
            $("#threadsdisplay").replaceWith(threads);
        }
    });
}

function updateActivities() {
    // Get parameters
    $.ajax({
        type: "GET",
        url: '/widget/activities',
        data: {},
        success: function(activities){
            $("#activitiesdisplay").replaceWith(activities);
        }
    });
}
