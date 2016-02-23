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
    })
    $('#groups,#forum,#following').click( function() {
       updateThreads();
    });
});

function updateMessages() {
    $.ajax({
        type: "GET",
        url: '/widget/messages',
        success: function(messages) {
            $("#messagesdisplay").html(messages);
        }
    });
}

function updateNotifications() {
    $.ajax({
        type: "GET",
        url: '/widget/notifications',
        success: function(notifications){
            $("#notificationsdisplay").html(notifications);
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
    var groups = $('#groups').prop('checked');
    var forum = $('#forum').prop('checked');
    var following = $('#following').prop('checked');
    $.ajax({
        type: "GET",
        url: '/widget/threads',
        data: {
            groups: groups,
            forum: forum,
            following: following
        },
        success: function(threads){
            $("#threadsdisplay").html(threads);
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
            $("#activitiesdisplay").html(threads);
        }
    });
}
