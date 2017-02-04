$(document).ready(function() {
    if (!$('#messagesdisplay').length) {
        return;
    }

    Home.updateMessages();
    Home.updateThreads();

    $('a[data-toggle="tab"]').on('show.bs.tab', Home.onTabChange);

    $('#all, #unread').change(function() {
        setTimeout(Home.updateMessages, 500);
    });

    $('#groups, #forum, #following').change(function() {
        setTimeout(Home.updateThreads, 500);
    });

    $('.hosting').click(Home.setHostingStatus);
});

var Home = {
    onTabChange: function (e) {
        switch($(e.target).attr('aria-controls')) {
            case 'messages':
                return Home.updateMessages();

            case 'notifications':
                return Home.updateNotifications();

            case 'threads':
                return Home.updateThreads();

            case 'activities':
                return Home.updateActivities();
        }
    },
    updateMessages: function () {
        var all = $('#all').hasClass('active') ? 1 : 0;
        var unread = $('#unread').hasClass('active') ? 1 : 0;

        $.ajax({
            type: 'GET',
            url: '/widget/messages',
            data: {
                all: all,
                unread: unread
            },
            success: function(messages) {
                $('#messagesdisplay').replaceWith(messages);
            }
        });
    },
    updateNotifications: function() {
        $.ajax({
            type: 'GET',
            url: '/widget/notifications',
            success: function (notifications) {
                $('#notificationsdisplay').replaceWith(notifications);

                // Set click event
                $('.notify').click(function () {
                    var that = $(this);
                    var id = $(this).attr('id');

                    $.ajax({
                        type: 'GET',
                        url: '/notify/' + id.replace('notify-', '') + '/check',
                        success: function() {
                            // update the notifications
                            Home.updateNotifications();
                        }
                    });
                });
            }
        });
    },
    updateThreads: function () {
        // Get parameters
        var groups = $('#groups').hasClass('active') ? 1 : 0;
        var forum = $('#forum').hasClass('active') ? 1 : 0;
        var following = $('#following').hasClass('active') ? 1 : 0;

        $.ajax({
            type: 'GET',
            url: '/widget/threads',
            data: {
                groups: groups,
                forum: forum,
                following: following
            },
            success: function (threads) {
                $('#threadsdisplay').replaceWith(threads);
            }
        });
    },
    updateActivities: function () {
        // Get parameters
        $.ajax({
            type: 'GET',
            url: '/widget/activities',
            data: {},
            success: function (activities) {
                $('#activitiesdisplay').replaceWith(activities);
            }
        });
    },
    setHostingStatus: function (e) {
        e.preventDefault();
        // Get parameters
        var accommodation = this.id;
        $.ajax({
            type: 'POST',
            url: '/widget/accommodation',
            data: {
                accommodation: accommodation
            },
            dataType: 'json',
            success: function (data) {
                $('#welcomeavatar').replaceWith(data.profilePictureWithAccommodation);
                $('#accommodation').replaceWith(data.accommodationHtml);
                $('.hosting').click(Home.setHostingStatus);
            }
        });
    }
};
