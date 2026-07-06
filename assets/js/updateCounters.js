const browserNotificationLastIdKey = 'bewelcomeBrowserNotificationLastId';
const browserNotificationInterval = 120000;

function updateCount() {
    const browserNotificationLastId = localStorage.getItem(browserNotificationLastIdKey) || 0;
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
    const previousLastId = localStorage.getItem(browserNotificationLastIdKey);
    if (!previousLastId) {
        if (latestId) {
            localStorage.setItem(browserNotificationLastIdKey, latestId);
        }
        return;
    }

    if (!('Notification' in window) || Notification.permission !== 'granted') {
        localStorage.setItem(browserNotificationLastIdKey, latestId);
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

    localStorage.setItem(browserNotificationLastIdKey, latestId);
}

const interval = setInterval(function () { updateCount(); }, browserNotificationInterval);

// Initial call
updateCount();
