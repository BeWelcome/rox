import {initializeSingleAutoComplete} from '../suggest/locations';

function onChange(element, result) {
    const locationFullName = document.getElementById('tiny_location_fullname');
    const locationName = document.getElementById('tiny_location_name');
    const locationGeonameId = document.getElementById('tiny_location_geoname_id');
    const locationLatitude = document.getElementById('tiny_location_latitude');
    const locationLongitude = document.getElementById('tiny_location_longitude');
    locationFullName.value = result.name.replaceAll("#", ", ");
    locationName.value = result.name.split("#")[0];
    locationGeonameId.value = result.id;
    locationLatitude.value = result.latitude;
    locationLongitude.value = result.longitude;
}

initializeSingleAutoComplete("/suggest/locations/all", 'js-location-picker', onChange);

document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('conversationsdisplay')) {
        return;
    }

    Home.updateMessages();
    Home.updateThreads();
    Home.updateTripLegs();

    const tabElements = document.querySelectorAll('a[data-bs-toggle="tab"]');
    tabElements.forEach(tab => {
        tab.addEventListener('show.bs.tab', Home.onTabChange);
    });

    const allRadio = document.getElementById('all');
    if (allRadio) {
        allRadio.addEventListener('change', function() {
            setTimeout(Home.updateMessages, 500);
        });
    }

    const unreadRadio = document.getElementById('unread');
    if (unreadRadio) {
        unreadRadio.addEventListener('change', function() {
            setTimeout(Home.updateMessages, 500);
        });
    }

    const groupsButton = document.getElementById('groupsButton');
    if (groupsButton) {
        groupsButton.addEventListener('change', function() {
            setTimeout(Home.updateThreads, 500);
        });
    }

    const forumButton = document.getElementById('forumButton');
    if (forumButton) {
        forumButton.addEventListener('change', function() {
            setTimeout(Home.updateThreads, 500);
        });
    }

    const followingButton = document.getElementById('followingButton');
    if (followingButton) {
        followingButton.addEventListener('change', function() {
            setTimeout(Home.updateThreads, 500);
        });
    }

    const hostingElements = document.querySelectorAll('.hosting');
    hostingElements.forEach(el => {
        el.addEventListener('click', Home.setHostingStatus);
    });

    const showOnline = document.getElementById('show_online');
    if (showOnline) {
        showOnline.addEventListener('change', function() {
            setTimeout(Home.updateActivities, 500);
        });
    }

    const tripsRadius = document.getElementById('trips_radius');
    if (tripsRadius) {
        tripsRadius.addEventListener('change', function() {
            setTimeout(Home.updateTripLegs, 500);
        });
    }
});

var Home = {
    onTabChange: function (e) {
        switch(e.target.getAttribute('aria-controls')) {
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
        const allRadio = document.getElementById('all');
        const all = allRadio && allRadio.classList.contains('active') ? 1 : 0;
        
        const unreadRadio = document.getElementById('unread');
        const unread = unreadRadio && unreadRadio.classList.contains('active') ? 1 : 0;

        const params = new URLSearchParams({
            all: all,
            unread: unread
        });

        fetch('/widget/conversations?' + params.toString())
            .then(response => response.text())
            .then(messages => {
                const display = document.getElementById('conversationsdisplay');
                if (display) {
                    display.outerHTML = messages;
                }
            });
    },
    updateNotifications: function() {
        fetch('/widget/notifications')
            .then(response => response.text())
            .then(notifications => {
                const display = document.getElementById('notificationsdisplay');
                if (display) {
                    display.outerHTML = notifications;
                }

                // Set click event
                const notifies = document.querySelectorAll('.notify');
                notifies.forEach(notify => {
                    notify.addEventListener('click', function (e) {
                        e.preventDefault();
                        const id = this.getAttribute('id');
                        
                        fetch('/notify/' + id.replace('notify-', '') + '/check')
                            .then(() => {
                                // update the notifications
                                Home.updateNotifications();
                            });
                    });
                });
            });
    },
    updateThreads: function () {
        // Get parameters
        const groupsButton = document.getElementById('groupsButton');
        const groups = groupsButton && groupsButton.classList.contains('active') ? 1 : 0;
        
        const forumButton = document.getElementById('forumButton');
        const forum = forumButton && forumButton.classList.contains('active') ? 1 : 0;
        
        const followingEl = document.getElementById('following');
        const following = followingEl && followingEl.classList.contains('active') ? 1 : 0;

        const params = new URLSearchParams({
            groups: groups,
            forum: forum,
            following: following
        });

        fetch('/widget/threads?' + params.toString())
            .then(response => response.text())
            .then(threads => {
                const display = document.getElementById('threadsdisplay');
                if (display) {
                    display.outerHTML = threads;
                }
            });
    },
    updateActivities: function () {
        const showOnline = document.getElementById('show_online');
        const online = showOnline && showOnline.checked ? 1 : 0;
        
        const params = new URLSearchParams({
            online: online
        });

        fetch('/widget/activities?' + params.toString())
            .then(response => response.text())
            .then(activities => {
                const display = document.getElementById('activitiesdisplay');
                if (display) {
                    display.outerHTML = activities;
                }
            });
    },
    updateTripLegs: function () {
        const tripsRadius = document.getElementById('trips_radius');
        const radius = tripsRadius ? tripsRadius.value : '';

        const params = new URLSearchParams({
            radius: radius
        });

        fetch('/widget/visitors?' + params.toString())
            .then(response => response.text())
            .then(legs => {
                const display = document.getElementById('legsdisplay');
                if (display) {
                    display.outerHTML = legs;
                }
            });
    },

    setHostingStatus: function (e) {
        e.preventDefault();
        const accommodation = this.id;
        
        const formData = new FormData();
        formData.append('accommodation', accommodation);

        fetch('/widget/accommodation', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const welcomeAvatar = document.getElementById('welcomeavatar');
            if (welcomeAvatar) {
                welcomeAvatar.outerHTML = data.profilePictureWithAccommodation;
            }
            
            const accommodationDisplay = document.getElementById('accommodation');
            if (accommodationDisplay) {
                accommodationDisplay.outerHTML = data.accommodationHtml;
            }
            
            const hostingElements = document.querySelectorAll('.hosting');
            hostingElements.forEach(el => {
                el.addEventListener('click', Home.setHostingStatus);
            });
        });
    }
};