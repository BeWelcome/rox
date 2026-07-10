import {initBrowserPushSession} from './browserPushPreference';

const browserNotificationLastIdKeyPrefix = 'bewelcomeBrowserNotificationLastId:';
const browserNotificationDefaultInterval = 600000;
const browserNotificationOpenOnlyInterval = 120000;
const browserNotificationMemoryLastIds = {};
let browserNotificationIntervalId = null;
let browserNotificationCurrentInterval = browserNotificationDefaultInterval;

function updateCount() {
    const memberId = getBrowserNotificationMemberId();
    const browserNotificationLastId = getBrowserNotificationLastId(memberId);
    const browserNotificationQuery = browserNotificationLastId === null
        ? ''
        : '?browserNotificationSince=' + encodeURIComponent(browserNotificationLastId);
    fetch('/count/conversations/unread' + browserNotificationQuery, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(async data => {
        const conversationCount = document.getElementById('conversationCount');
        if (conversationCount && data && data.html !== undefined) {
            conversationCount.outerHTML = data.html;
            if (typeof window.autocollapse_menu === "function") {
                window.autocollapse_menu(true);
            }
        }
        await showBrowserNotifications(data);
        updateBrowserNotificationInterval(data && data.browserNotification ? browserNotificationOpenOnlyInterval : browserNotificationDefaultInterval);
    })
    .catch(error => {
        console.error('Error fetching unread count:', error);
    });
}

async function showBrowserNotifications(data) {
    if (!data || !data.browserNotification) {
        return;
    }

    const latestId = data.browserNotification.latestId || 0;
    const memberId = data.browserNotification.memberId || getBrowserNotificationMemberId();
    const previousLastId = getBrowserNotificationLastId(memberId);
    if (previousLastId === null) {
        setBrowserNotificationLastId(memberId, latestId);
        return;
    }

    if (!('Notification' in window) || Notification.permission !== 'granted') {
        setBrowserNotificationLastId(memberId, latestId);
        return;
    }

    let displayedThroughId = previousLastId;
    for (const notification of data.browserNotification.notifications) {
        if (notification.id <= previousLastId) {
            continue;
        }
        try {
            await showBrowserNotification(notification);
            displayedThroughId = notification.id;
        } catch (error) {
            break;
        }
    }

    setBrowserNotificationLastId(memberId, displayedThroughId);
}

async function showBrowserNotification(notification) {
    const options = {
        body: notification.body,
        tag: 'bewelcome-open-' + notification.id,
        data: {url: notification.url},
        icon: '/images/icon-192x192.png',
        badge: '/images/icon-96x96.png',
    };
    if ('serviceWorker' in navigator) {
        const registration = await withTimeout(navigator.serviceWorker.ready, 5000);
        if (registration && 'function' === typeof registration.showNotification) {
            await registration.showNotification(notification.title, options);
            return;
        }
    }

    const browserNotification = new Notification(notification.title, options);
    browserNotification.onclick = function () {
        const url = new URL(notification.url, window.location.origin);
        if (url.origin === window.location.origin) {
            window.focus();
            window.location.href = url.href;
        }
    };
}

function getBrowserNotificationMemberId() {
    const conversationCount = document.getElementById('conversationCount');

    return conversationCount ? conversationCount.dataset.memberId || '' : '';
}

function getBrowserNotificationLastId(memberId) {
    if (!memberId) {
        return null;
    }

    const value = getBrowserNotificationStoredValue(browserNotificationLastIdKeyPrefix + memberId);
    if (value === null) {
        return null;
    }

    const lastId = Number(value);
    return Number.isFinite(lastId) ? lastId : 0;
}

function setBrowserNotificationLastId(memberId, lastId) {
    if (!memberId) {
        return;
    }

    setBrowserNotificationStoredValue(browserNotificationLastIdKeyPrefix + memberId, lastId);
}

function getBrowserNotificationStoredValue(key) {
    try {
        return window.sessionStorage.getItem(key) ?? browserNotificationMemoryLastIds[key] ?? null;
    } catch (error) {
        return browserNotificationMemoryLastIds[key] ?? null;
    }
}

function setBrowserNotificationStoredValue(key, value) {
    browserNotificationMemoryLastIds[key] = String(value);
    try {
        window.sessionStorage.setItem(key, value);
    } catch (error) {
        // Storage can be blocked; the in-memory marker still prevents duplicates in this tab.
    }
}

function clearBrowserNotificationLastId(memberId) {
    if (!memberId) {
        return;
    }

    const key = browserNotificationLastIdKeyPrefix + memberId;
    delete browserNotificationMemoryLastIds[key];
    try {
        window.sessionStorage.removeItem(key);
    } catch (error) {
        // The next request still initializes from the in-memory marker.
    }
}

function updateBrowserNotificationInterval(interval) {
    if (interval === browserNotificationCurrentInterval && browserNotificationIntervalId) {
        return;
    }

    window.clearInterval(browserNotificationIntervalId);
    browserNotificationCurrentInterval = interval;
    browserNotificationIntervalId = window.setInterval(function () { updateCount(); }, interval);
}

function withTimeout(promise, timeoutMs) {
    return Promise.race([
        promise,
        new Promise((resolve) => {
            window.setTimeout(() => resolve(null), timeoutMs);
        }),
    ]);
}

window.addEventListener('browser-push-preference-changed', (event) => {
    const memberId = getBrowserNotificationMemberId();
    clearBrowserNotificationLastId(memberId);
    const interval = event.detail && event.detail.value === 'OpenOnly'
        ? browserNotificationOpenOnlyInterval
        : browserNotificationDefaultInterval;
    updateBrowserNotificationInterval(interval);
    updateCount();
});

window.addEventListener('browser-push-session-ending', (event) => {
    clearBrowserNotificationLastId(event.detail && event.detail.memberId);
});

initBrowserPushSession();

if (getBrowserNotificationMemberId()) {
    updateBrowserNotificationInterval(browserNotificationDefaultInterval);
    updateCount();
}
