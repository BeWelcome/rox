const browserNotificationLastIdKeyPrefix = 'bewelcomeBrowserNotificationLastId:';
const browserNotificationDefaultInterval = 600000;
const browserNotificationOpenOnlyInterval = 120000;
const browserNotificationMemoryLastIds = {};
let browserNotificationIntervalId = null;
let browserNotificationCurrentInterval = browserNotificationDefaultInterval;

function updateCount() {
    const browserNotificationLastId = getBrowserNotificationLastId(getBrowserNotificationMemberId());
    fetch('/count/conversations/unread?browserNotificationSince=' + encodeURIComponent(browserNotificationLastId), {
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
    .then(data => {
        const conversationCount = document.getElementById('conversationCount');
        if (conversationCount && data && data.html !== undefined) {
            conversationCount.outerHTML = data.html;
            if (typeof window.autocollapse_menu === "function") {
                window.autocollapse_menu(true);
            }
        }
        showBrowserNotifications(data);
        updateBrowserNotificationInterval(data && data.browserNotification ? browserNotificationOpenOnlyInterval : browserNotificationDefaultInterval);
    })
    .catch(error => {
        console.error('Error fetching unread count:', error);
    });
}

function showBrowserNotifications(data) {
    if (!data || !data.browserNotification) {
        return;
    }

    const latestId = data.browserNotification.latestId || 0;
    const memberId = data.browserNotification.memberId || getBrowserNotificationMemberId();
    const previousLastId = getBrowserNotificationLastId(memberId);
    if (!previousLastId) {
        if (latestId) {
            setBrowserNotificationLastId(memberId, latestId);
        }
        return;
    }

    if (!('Notification' in window) || Notification.permission !== 'granted') {
        setBrowserNotificationLastId(memberId, latestId);
        return;
    }

    data.browserNotification.notifications.forEach(notification => {
        if (notification.id <= previousLastId) {
            return;
        }
        const browserNotification = new Notification(notification.title, {
            body: notification.body,
            tag: 'bewelcome-open-' + notification.id,
        });
        browserNotification.onclick = function () {
            const url = new URL(notification.url, window.location.origin);
            if (url.origin === window.location.origin) {
                window.focus();
                window.location.href = url.href;
            }
        };
    });

    setBrowserNotificationLastId(memberId, latestId);
}

function getBrowserNotificationMemberId() {
    const conversationCount = document.getElementById('conversationCount');

    return conversationCount ? conversationCount.dataset.memberId || '' : '';
}

function getBrowserNotificationLastId(memberId) {
    if (!memberId) {
        return 0;
    }

    const value = getBrowserNotificationStoredValue(browserNotificationLastIdKeyPrefix + memberId);
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
        return window.localStorage.getItem(key) || browserNotificationMemoryLastIds[key] || null;
    } catch (error) {
        return browserNotificationMemoryLastIds[key] || null;
    }
}

function setBrowserNotificationStoredValue(key, value) {
    browserNotificationMemoryLastIds[key] = String(value);
    try {
        window.localStorage.setItem(key, value);
    } catch (error) {
        // Storage can be blocked; the in-memory marker still prevents duplicates in this tab.
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

updateBrowserNotificationInterval(browserNotificationDefaultInterval);

// Initial call
updateCount();
