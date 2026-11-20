import {initializeSingleAutoComplete} from '../suggest/locations';
import {initMemberSuggest} from '../member/suggest';
import './countries';
import {initContactDrawer} from '../contact/drawer';

// Visitors: write to a traveller or invite them in a side panel, without leaving the page
initContactDrawer('home');

// Messages received: the one opened in the panel is read now, its unread look goes
document.addEventListener('click', (event) => {
    const item = event.target.closest('.js-home-message.is-unread');
    if (item && event.button === 0 && !event.metaKey && !event.ctrlKey && !event.shiftKey && !event.altKey) {
        item.classList.remove('is-unread');
        item.querySelector('.c-home-item__dot')?.remove();
        // A new request is now just waiting: grey like in the lists
        item.querySelectorAll('.c-req-kind--new, .c-req-pill--new').forEach((part) => {
            part.className = part.className.replace(/--new\b/, '--pending');
        });
        // One unread less on the Messages card
        const count = document.querySelector('.c-home-dash__count--hot');
        if (count) {
            const left = (parseInt(count.textContent, 10) || 0) - 1;
            if (left > 0) {
                count.textContent = String(left);
            } else {
                count.remove();
            }
        }
        readPending = true;
    }
}, true);
// Once the conversation is open in the panel (it is marked read then), the envelope in the
// top bar asks for its count again (updateCounters.js)
let readPending = false;
window.addEventListener('message', (event) => {
    if (readPending && event.origin === window.location.origin && event.data && event.data.type === 'bw-contact-ready') {
        readPending = false;
        window.dispatchEvent(new CustomEvent('bw-conversations-read'));
    }
});

// The side panel closed: an answer may have been sent there, so the messages, the activities
// and the "requests to answer" line under the banner are loaded again
window.addEventListener('bw-contact-closed', () => {
    Home.updateMessages();
    // An activity created or answered there
    Home.updateActivities();
    const alert = document.querySelector('.js-home-alert');
    if (alert) {
        fetch('/widget/requests-alert', { credentials: 'same-origin' })
            .then((response) => (response.ok ? response.text() : null))
            .then((html) => {
                if (html !== null) {
                    alert.innerHTML = html;
                }
            })
            .catch(() => {});
    }
});

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
    const locationAdminUnit = document.getElementById('tiny_location_admin_unit');
    if (locationAdminUnit) {
        locationAdminUnit.value = result.isAdminUnit;
    }

    // Picking a place opens the search by location for it straight away.
    const form = element.form;
    if (form) {
        form.requestSubmit(document.getElementById('tiny_updateMap') || undefined);
    }
}

initializeSingleAutoComplete("/suggest/locations/all", 'js-location-picker', onChange);

document.addEventListener('DOMContentLoaded', function() {
    if (!document.getElementById('conversationsdisplay')) {
        return;
    }

    Home.updateMessages();
    Home.updateGroups();
    Home.updateTripLegs();
    Home.updateActivities();

    const tabElements = document.querySelectorAll('a[data-bs-toggle="tab"]');
    tabElements.forEach(tab => {
        tab.addEventListener('show.bs.tab', Home.onTabChange);
    });
    const tabFromHash = Array.from(tabElements).find(tab => tab.getAttribute('href') === window.location.hash);
    if (tabFromHash) {
        window.bootstrap.Tab.getOrCreateInstance(tabFromHash).show();
    }

    // Filter pills of the messages: All / Unread pick one, and the list is reloaded.
    document.querySelectorAll('.js-home-filter').forEach(function(pill) {
        pill.addEventListener('click', function() {
            if (pill.dataset.group === 'messages') {
                document.querySelectorAll('.js-home-filter[data-group="messages"]').forEach(function(other) {
                    other.classList.toggle('active', other === pill);
                    other.setAttribute('aria-checked', other === pill ? 'true' : 'false');
                });
                Home.updateMessages();
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
            initHelloMemberSearch(username, list);
        }
    }

    document.querySelectorAll('.js-home-slider').forEach(initHomeSlider);

    const helloToggle = document.querySelector('.js-hello-toggle');
    if (helloToggle) {
        initHelloToggle(helloToggle.closest('.l-hello'), helloToggle);
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

    // The trips card's distance: an entry of its dropdown sets #trips_radius, the pill's label and the check
    document.querySelectorAll('.js-home-trips-radius-option').forEach(function (option) {
        option.addEventListener('click', function () {
            const input = document.getElementById('trips_radius');
            if (option.dataset.value === input.value) {
                return;
            }
            input.value = option.dataset.value;
            const wrap = option.closest('.c-home-dash__radius-wrap');
            wrap.querySelector('.js-home-trips-radius-label').textContent = option.textContent.trim();
            wrap.querySelectorAll('.js-home-trips-radius-option').forEach(function (other) {
                other.classList.toggle('is-active', other === option);
                other.setAttribute('aria-pressed', other === option ? 'true' : 'false');
            });
            Home.updateTripLegs();
        });
    });

    // Visitors: the distance is a few quick choices that set the hidden #trips_radius
    document.querySelectorAll('.js-trips-radius').forEach(function (group) {
        const input = group.querySelector('#trips_radius');
        group.addEventListener('click', function (event) {
            const option = event.target.closest('[data-radius]');
            if (!option || option.dataset.radius === input.value) {
                return;
            }
            group.querySelectorAll('[data-radius]').forEach(function (other) {
                other.classList.toggle('is-active', other === option);
                other.setAttribute('aria-pressed', other === option ? 'true' : 'false');
            });
            input.value = option.dataset.radius;
            input.dispatchEvent(new Event('change'));
        });
    });
});

var Home = {
    onTabChange: function (e) {
        switch(e.target.getAttribute('aria-controls')) {
            case 'messages':
                return Home.updateMessages();

            case 'groups':
                return Home.updateGroups();

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
                // No messages at all: the link to the inbox has nothing to offer
                const list = document.getElementById('conversationsdisplay');
                const pane = list ? list.closest('.c-home-pane') : null;
                if (pane) {
                    pane.classList.toggle('is-empty', list.hasAttribute('data-empty'));
                }
            });
    },
    // The My groups tab: threads of the member's groups, the BeWelcome group's agora included.
    updateGroups: function () {
        fetch('/widget/threads')
            .then(response => response.text())
            .then(threads => {
                const display = document.getElementById('groupsdisplay');
                if (display) {
                    display.outerHTML = threads;
                }
                // No discussions: the line offers to find groups, All discussions has nothing to show
                const list = document.getElementById('groupsdisplay');
                list?.closest('.c-home-pane')?.classList.toggle('is-empty', list.hasAttribute('data-empty'));
            });
    },
    updateActivities: function () {
        // The member's choice to show online activities (a preference, set on the activities page)
        const showOnline = document.getElementById('show_online');
        const card = document.getElementById('activitiesdisplay')?.closest('[data-online]');
        const online = (showOnline ? showOnline.checked : card && card.dataset.online === '1') ? 1 : 0;
        
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
                // None: the card becomes a thin strip at the bottom
                const list = document.getElementById('activitiesdisplay');
                list?.closest('.js-home-activities')?.classList.toggle('is-empty', list.hasAttribute('data-empty'));
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
                initTripsSlider(document.getElementById('legsdisplay'));
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
            
            // Status under the name on phones: "Hosting" in green, or "Not hosting".
            const status = document.querySelector('.js-hello-status');
            const hostingSwitch = document.querySelector('#accommodation .l-hosting-switch');
            if (status && hostingSwitch) {
                const isOn = hostingSwitch.classList.contains('is-on');
                status.classList.toggle('is-on', isOn);
                status.textContent = isOn ? status.dataset.on : status.dataset.off;
            }

            const hostingElements = document.querySelectorAll('.hosting');
            hostingElements.forEach(el => {
                el.addEventListener('click', Home.setHostingStatus);
            });
        });
    }
};


/**
 * The "Find a member" search of the welcome banner: suggestions under the field (member/suggest), and a member
 * picked or typed opens their profile.
 */
function initHelloMemberSearch(input, list) {
    const form = input.form;
    const goTo = (name) => {
        window.location.href = form.dataset.profileUrl.replace('__username__', encodeURIComponent(name));
    };
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const name = input.value.trim();
        if (name !== '') {
            goTo(name);
        }
    });
    initMemberSuggest(input, list, goTo);
}

/**
 * Phones: fold the welcome banner panel (hosting switch, counters and the
 * search) or unfold it. The choice is kept in localStorage, and read
 * again by an inline script in the template so the page does not jump on load.
 */
function initHelloToggle(banner, button) {
    const storageKey = 'bw.hello.collapsed';
    const phone = window.matchMedia('(max-width: 575.98px)');

    const sync = () => {
        const collapsed = banner.classList.contains('is-collapsed');
        const label = collapsed ? button.dataset.expand : button.dataset.collapse;
        button.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        button.setAttribute('aria-label', label);
        // No title: phones show it as a box on tap
        button.removeAttribute('title');
    };

    // The panel under the name (hosting switch) and the search slide up and down
    // with an ease-in-out on their real height, measured before and after the change.
    const parts = [banner.querySelector('.l-hello__me .l-hosting'), banner.querySelector('.js-hello-search')]
        .filter((part) => part);
    const easing = 'cubic-bezier(0.65, 0, 0.35, 1)';
    let running = [];
    const box = (part) => {
        const style = getComputedStyle(part);
        return {
            height: `${part.getBoundingClientRect().height}px`,
            marginTop: style.marginTop,
            paddingTop: style.paddingTop,
            paddingBottom: style.paddingBottom,
            opacity: style.opacity,
        };
    };

    const toggle = () => {
        const animate = phone.matches && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        running.forEach((animation) => animation.cancel());
        running = [];
        const from = animate ? parts.map(box) : [];
        const collapsed = banner.classList.toggle('is-collapsed');
        if (animate) {
            const to = parts.map(box);
            banner.classList.add('is-animating');
            const batch = parts.map((part, index) => part.animate([from[index], to[index]], { duration: 420, easing }));
            running = batch;
            // A click during the slide cancels this one and starts another: only the last one ends the slide
            const done = () => {
                if (running === batch) {
                    banner.classList.remove('is-animating');
                    running = [];
                }
            };
            Promise.all(batch.map((animation) => animation.finished)).then(done, done);
        }
        try {
            localStorage.setItem(storageKey, collapsed ? '1' : '0');
        } catch (e) {
            // Storage blocked: the panel still folds, the choice is just not kept.
        }
        sync();
    };

    sync();
    button.addEventListener('click', toggle);

    // On phones the whole identity line (avatar, name, city) opens and closes
    // the panel too; the hosting switch inside it keeps its own action.
    const identity = banner.querySelector('.js-hello-me');
    if (identity) {
        identity.addEventListener('click', (event) => {
            if (!phone.matches || button.contains(event.target) || event.target.closest('.l-hosting')) {
                return;
            }
            event.preventDefault();
            toggle();
        });
    }
}

/**
 * "Be informed" box: pages of links scrolled sideways. The buttons move one
 * page; a swipe works too (scroll snap). Buttons and dots follow the scroll.
 */
function initHomeSlider(slider) {
    const track = slider.querySelector('.js-home-slider-track');
    const prev = slider.querySelector('.js-home-slider-prev');
    const next = slider.querySelector('.js-home-slider-next');
    const dots = slider.querySelectorAll('.c-home-slider__dot');
    if (!track || !prev || !next) {
        return;
    }
    const pages = track.children.length;

    const current = () => Math.round(track.scrollLeft / Math.max(track.clientWidth, 1));
    const update = () => {
        const page = current();
        prev.disabled = page <= 0;
        next.disabled = page >= pages - 1;
        dots.forEach((dot, index) => dot.classList.toggle('is-active', index === page));
    };
    const go = (step) => {
        const page = Math.min(Math.max(current() + step, 0), pages - 1);
        track.scrollTo({ left: page * track.clientWidth });
    };

    prev.addEventListener('click', () => go(-1));
    next.addEventListener('click', () => go(1));
    let frame = null;
    track.addEventListener('scroll', () => {
        if (frame === null) {
            frame = requestAnimationFrame(() => {
                frame = null;
                update();
            });
        }
    }, { passive: true });
    window.addEventListener('resize', update);
    update();
}

/*
 * Trips card: one trip at a time, scrolled sideways. The arrows move one trip, a swipe works
 * too (scroll snap); the travellers' faces under it follow and take to their trip, the head shows how many come.
 */
function initTripsSlider(list) {
    // Nobody comes: no card
    const card = list ? list.closest('.js-home-trips') : null;
    if (card) {
        card.hidden = list.dataset.count === '0';
    }
    const position = document.querySelector('.js-home-trips-position');
    const track = list ? list.querySelector('.js-home-trips-track') : null;
    const count = list ? Number(list.dataset.count || 0) : 0;
    // How many come, in the card's head
    if (position) {
        position.hidden = count < 1;
        position.textContent = count;
    }
    if (!track) {
        return;
    }
    const prev = list.querySelector('.js-home-trips-prev');
    const next = list.querySelector('.js-home-trips-next');
    const dots = list.querySelectorAll('.js-home-trips-goto');
    const current = () => Math.round(track.scrollLeft / Math.max(track.clientWidth, 1));
    const update = () => {
        const page = Math.min(current(), count - 1);
        if (prev) prev.disabled = page <= 0;
        if (next) next.disabled = page >= count - 1;
        dots.forEach((dot, index) => dot.classList.toggle('is-active', index === page));
        // Many travellers: their row scrolls to keep the current face in view (not the page)
        const face = dots[page];
        const row = face ? face.parentElement : null;
        if (row && row.scrollWidth > row.clientWidth) {
            row.scrollLeft = face.offsetLeft - row.offsetLeft - (row.clientWidth - face.offsetWidth) / 2;
        }
    };
    const go = (step) => {
        const page = Math.min(Math.max(current() + step, 0), count - 1);
        track.scrollTo({ left: page * track.clientWidth });
    };
    dots.forEach((dot) => dot.addEventListener('click', () => {
        track.scrollTo({ left: Number(dot.dataset.index) * track.clientWidth });
    }));
    prev?.addEventListener('click', () => go(-1));
    next?.addEventListener('click', () => go(1));
    let frame = null;
    track.addEventListener('scroll', () => {
        if (frame === null) {
            frame = requestAnimationFrame(() => {
                frame = null;
                update();
            });
        }
    }, { passive: true });
    window.addEventListener('resize', update);
    update();
}

// BeWelcome News on phones: the cards scroll sideways, < and > move by one card
const news = document.querySelector('.js-home-news');
const newsPrev = document.querySelector('.js-home-news-prev');
const newsNext = document.querySelector('.js-home-news-next');
if (news && newsPrev && newsNext) {
    const step = () => {
        const card = news.firstElementChild;
        return card ? card.getBoundingClientRect().width + parseFloat(getComputedStyle(news).columnGap || '0') : news.clientWidth;
    };
    const updateNewsButtons = () => {
        newsPrev.disabled = news.scrollLeft <= 2;
        newsNext.disabled = news.scrollLeft + news.clientWidth >= news.scrollWidth - 2;
    };
    newsPrev.addEventListener('click', () => news.scrollBy({left: -step(), behavior: 'smooth'}));
    newsNext.addEventListener('click', () => news.scrollBy({left: step(), behavior: 'smooth'}));
    news.addEventListener('scroll', updateNewsButtons, {passive: true});
    window.addEventListener('resize', updateNewsButtons);
    updateNewsButtons();
}

// Donate box: Donate opens PayPal's window over the page (its Donate SDK, the same button as
// on the donations page, rendered hidden and clicked for the member). Once PayPal says it is
// done, the donation is recorded (/donation/finish) and a thank you replaces the box.
const donateBox = document.querySelector('.js-home-donate');
if (donateBox) {
    const open = donateBox.querySelector('.js-home-donate-open');
    let sdk = null;
    const loadSdk = () => {
        if (!sdk) {
            sdk = new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://www.paypalobjects.com/donate/sdk/donate-sdk.js';
                script.charset = 'UTF-8';
                script.onload = () => {
                    window.PayPal.Donation.Button({
                        env: 'production',
                        hosted_button_id: '58STYCEPBD79S',
                        image: {
                            src: 'https://www.paypalobjects.com/en_US/FR/i/btn/btn_donateCC_LG.gif',
                            alt: 'Donate with PayPal',
                        },
                        onComplete: finishDonation,
                    }).render('#home-donate-paypal');
                    resolve();
                };
                script.onerror = reject;
                document.head.appendChild(script);
            });
        }
        return sdk;
    };
    const finishDonation = (param) => {
        // PayPal says to check that it is an object
        if (typeof param !== 'object' || Array.isArray(param) || param === null) {
            return;
        }
        param.nonce = open.dataset.nonce;
        fetch(open.dataset.finish, {
            method: 'POST',
            headers: {'Accept': 'application/json', 'Content-Type': 'application/json'},
            body: JSON.stringify(param),
        })
            .then((response) => response.json())
            .then((details) => {
                if (details.success !== true) {
                    throw new Error('not recorded');
                }
                // Plausible revenue goal "Donation" (#540), as the donations page does
                const amount = parseFloat(param.amt || 0);
                if (window.plausible && amount > 0) {
                    window.plausible('Donation', {revenue: {currency: String(param.cc || 'EUR').toUpperCase(), amount}});
                }
                donateBox.querySelector('.js-home-donate-body').hidden = true;
                donateBox.querySelector('.js-home-donate-thanks').hidden = false;
                donateBox.classList.add('is-thanked');
            })
            .catch(() => {
                window.location.href = open.dataset.fallback;
            });
    };
    // Loaded early (on hover or focus) so the click itself can open PayPal's window
    ['pointerenter', 'focus', 'touchstart'].forEach((type) => open.addEventListener(type, () => { loadSdk().catch(() => {}); }, {once: true, passive: true}));
    open.addEventListener('click', () => {
        const paypalButton = donateBox.querySelector('#home-donate-paypal img, #home-donate-paypal button, #home-donate-paypal a');
        if (paypalButton) {
            paypalButton.click();
            return;
        }
        // Not ready (or blocked): the donations page
        window.location.href = open.dataset.fallback;
    });
}
