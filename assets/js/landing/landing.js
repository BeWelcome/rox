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
    const tabFromHash = Array.from(tabElements).find(tab => tab.getAttribute('href') === window.location.hash);
    if (tabFromHash) {
        window.bootstrap.Tab.getOrCreateInstance(tabFromHash).show();
    }

    // Filter pills: All / Unread pick one, Groups / Forum switch on and off.
    // The lists are reloaded with the pills that carry the "active" class.
    document.querySelectorAll('.js-home-filter').forEach(function(pill) {
        pill.addEventListener('click', function() {
            if (pill.dataset.group === 'messages') {
                document.querySelectorAll('.js-home-filter[data-group="messages"]').forEach(function(other) {
                    other.classList.toggle('active', other === pill);
                    other.setAttribute('aria-checked', other === pill ? 'true' : 'false');
                });
                Home.updateMessages();
            } else {
                pill.classList.toggle('active');
                pill.setAttribute('aria-pressed', pill.classList.contains('active') ? 'true' : 'false');
                Home.updateThreads();
            }
        });
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

    // Welcome banner: switch the search between a place and a member.
    const helloSearch = document.querySelector('.js-hello-search');
    if (helloSearch) {
        helloSearch.querySelectorAll('.js-hello-mode').forEach(button => {
            button.addEventListener('click', function() {
                const mode = this.dataset.mode;
                helloSearch.dataset.mode = mode;
                helloSearch.querySelectorAll('.js-hello-mode').forEach(other => {
                    const isActive = other === this;
                    other.classList.toggle('active', isActive);
                    other.setAttribute('aria-checked', isActive ? 'true' : 'false');
                });
                helloSearch.querySelectorAll('.js-hello-mode-place').forEach(el => { el.hidden = mode !== 'place'; });
                helloSearch.querySelectorAll('.js-hello-mode-member').forEach(el => { el.hidden = mode !== 'member'; });
                const field = helloSearch.querySelector(mode === 'member' ? '.js-hello-username' : '.js-location-picker');
                if (field) {
                    field.focus();
                }
            });
        });

        // Suggest usernames while typing, in our own list under the field.
        const username = helloSearch.querySelector('.js-hello-username');
        const list = helloSearch.querySelector('.js-hello-suggest');
        if (username && list) {
            initMemberSuggest(username, list);
        }
    }

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

document.addEventListener('submit', function(event) {
    const form = event.target.closest('.js-hide-leg');
    if (!form) {
        return;
    }

    event.preventDefault();

    fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    }).then(function(response) {
        if (response.ok) {
            form.closest('.c-dashboard__item').remove();
        }
    });
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
        const accommodation = this.dataset.accommodation || this.id;
        
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


/**
 * Member suggestions for the welcome banner: a listbox under the field with
 * avatars, a loader while searching and keyboard navigation.
 */
function initMemberSuggest(input, list) {
    const form = input.form;
    let timer = null;
    let controller = null;
    let active = -1;

    const open = () => {
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };
    const close = () => {
        list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        active = -1;
    };
    const status = (text, loading) => {
        const item = document.createElement('li');
        item.className = 'l-hello__suggest-status';
        if (loading) {
            const spinner = document.createElement('span');
            spinner.className = 'l-hello__spinner';
            spinner.setAttribute('aria-hidden', 'true');
            item.append(spinner);
        }
        item.append(document.createTextNode(text));
        list.replaceChildren(item);
        open();
    };
    const options = () => Array.from(list.querySelectorAll('[role="option"]'));
    const highlight = (index) => {
        const all = options();
        all.forEach((option, i) => option.setAttribute('aria-selected', i === index ? 'true' : 'false'));
        active = index;
        if (all[index]) {
            input.setAttribute('aria-activedescendant', all[index].id);
            all[index].scrollIntoView({ block: 'nearest' });
        }
    };
    const pick = (value) => {
        input.value = value;
        close();
        form.requestSubmit();
    };
    const render = (items, term) => {
        if (items.length === 0) {
            status(list.dataset.empty, false);
            return;
        }
        const lower = term.toLowerCase();
        list.replaceChildren(...items.map((item, i) => {
            const name = String(item.value);
            const option = document.createElement('li');
            option.id = 'hello_username_option_' + i;
            option.className = 'l-hello__suggest-item';
            option.setAttribute('role', 'option');
            option.setAttribute('aria-selected', 'false');
            option.dataset.value = name;

            const avatar = document.createElement('img');
            avatar.src = '/members/avatar/' + encodeURIComponent(name) + '/48';
            avatar.alt = '';
            avatar.width = 32;
            avatar.height = 32;
            avatar.loading = 'lazy';

            const label = document.createElement('span');
            label.className = 'l-hello__suggest-name';
            const at = name.toLowerCase().indexOf(lower);
            if (at >= 0 && lower !== '') {
                const strong = document.createElement('strong');
                strong.textContent = name.substr(at, term.length);
                label.append(name.substr(0, at), strong, name.substr(at + term.length));
            } else {
                label.textContent = name;
            }

            option.append(avatar, label);
            return option;
        }));
        open();
        active = -1;
    };

    input.addEventListener('input', () => {
        clearTimeout(timer);
        if (controller) {
            controller.abort();
        }
        const term = input.value.trim();
        if (term.length < 2) {
            close();
            list.replaceChildren();
            return;
        }
        status(list.dataset.loading, true);
        timer = setTimeout(() => {
            controller = new AbortController();
            fetch('/member/autocomplete/start?term=' + encodeURIComponent(term), { signal: controller.signal })
                .then(response => response.json())
                .then(json => render(json.items || [], term))
                .catch(error => {
                    if (error.name !== 'AbortError') {
                        status(list.dataset.empty, false);
                    }
                });
        }, 250);
    });

    input.addEventListener('keydown', (event) => {
        const all = options();
        if (list.hidden || all.length === 0) {
            return;
        }
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            highlight((active + 1) % all.length);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            highlight(active <= 0 ? all.length - 1 : active - 1);
        } else if (event.key === 'Enter' && active >= 0) {
            event.preventDefault();
            pick(all[active].dataset.value);
        } else if (event.key === 'Escape') {
            close();
        }
    });

    // mousedown keeps the focus in the field, so the list is not closed by blur first
    list.addEventListener('mousedown', (event) => {
        const option = event.target.closest('[role="option"]');
        event.preventDefault();
        if (option) {
            pick(option.dataset.value);
        }
    });
    list.addEventListener('mouseover', (event) => {
        const option = event.target.closest('[role="option"]');
        if (option) {
            highlight(options().indexOf(option));
        }
    });
    input.addEventListener('blur', close);
    input.addEventListener('focus', () => {
        if (list.children.length > 0 && input.value.trim().length >= 2) {
            open();
        }
    });
}
