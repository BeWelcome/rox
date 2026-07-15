import { precacheAndRoute } from 'workbox-precaching';
import { NetworkFirst } from 'workbox-strategies';
import { CacheableResponsePlugin } from 'workbox-cacheable-response';
import { registerRoute } from 'workbox-routing';
import { ExpirationPlugin } from 'workbox-expiration';

const BROWSER_PUSH_LOGOUT_TIMEOUT_MS = 5000;

precacheAndRoute(self.__WB_MANIFEST);

addEventListener('install', event => {
    skipWaiting();
});

addEventListener("message", event => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        skipWaiting();
    }
});

self.addEventListener('push', event => {
    const payload = getPushPayload(event);
    const url = getSameOriginPath(payload.url);

    event.waitUntil(
        self.registration.showNotification(payload.title || 'BeWelcome', {
            body: payload.body || '',
            data: { url },
            icon: '/images/icon-192x192.png',
            badge: '/images/icon-96x96.png',
        })
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();
    const url = getSameOriginPath(event.notification.data && event.notification.data.url);
    const absoluteUrl = new URL(url, self.location.origin).href;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(windowClients => {
                for (const client of windowClients) {
                    if (client.url === absoluteUrl && 'focus' in client) {
                        return client.focus();
                    }
                }

                return clients.openWindow(url);
            })
    );
});

function getPushPayload(event) {
    if (!event.data) {
        return {};
    }

    try {
        return event.data.json();
    } catch (error) {
        return {};
    }
}

function getSameOriginPath(url) {
    try {
        const target = new URL(url || '/', self.location.origin);
        if (target.origin !== self.location.origin) {
            return '/';
        }

        return target.pathname + target.search + target.hash;
    } catch (error) {
        return '/';
    }
}

registerRoute(
    ({ request, url }) =>
        request.mode === 'navigate' &&
        url.origin === self.location.origin &&
        url.pathname === '/logout',
    async ({ request }) => {
        await unsubscribeBrowserPushBeforeLogout();

        return fetch(request);
    },
);

async function unsubscribeBrowserPushBeforeLogout() {
    try {
        const subscription = await self.registration.pushManager.getSubscription();
        if (subscription) {
            await withTimeout(subscription.unsubscribe(), BROWSER_PUSH_LOGOUT_TIMEOUT_MS);
        }
    } catch (error) {
        // Logout must still proceed if local push cleanup fails.
    }
}

async function withTimeout(promise, timeoutMs) {
    let timeoutId;
    try {
        return await Promise.race([
            promise,
            new Promise(resolve => {
                timeoutId = setTimeout(resolve, timeoutMs);
            }),
        ]);
    } finally {
        clearTimeout(timeoutId);
    }
}

// Always try to read the landing page from the network
registerRoute(
    ({url}) => url.pathname === '/',
    new NetworkFirst()
);

registerRoute(
    ({ url }) =>
        url.destination === 'style' ||
        url.destination === 'script' ||
        url.destination === 'worker',
    new NetworkFirst({
        cacheName: 'assets',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [200],
            }),
        ],
    }),
);

/* cache build assets for a year */
registerRoute(
    new RegExp('/build/.*'),
    new NetworkFirst({
        cacheName: 'assets',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [200],
            }),
            new ExpirationPlugin({
                maxEntries: 100,
                maxAgeSeconds: 60 * 60 * 24 * 365,
            }),
        ],
    }),
);

registerRoute(
    new RegExp('/conversation/.*'),
    new NetworkFirst({
        cacheName: 'conversations',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [200],
            }),
            new ExpirationPlugin({
                maxEntries: 100,
                maxAgeSeconds: 60 * 60 * 24 * 10,
            }),
        ],
    }),
);

registerRoute(
    new RegExp('/conversations/.*'),
    new NetworkFirst({
        cacheName: 'conversations',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [200],
            }),
            new ExpirationPlugin({
                maxEntries: 100,
                maxAgeSeconds: 60 * 60 * 24 * 10,
            }),
        ],
    }),
);

// Cache members profiles a Cache First strategy for 10 days
registerRoute(
    ({url}) => url.pathname.startsWith('/members'),
    new NetworkFirst({
        cacheName: 'members',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [200],
            }),
            new ExpirationPlugin({
                maxEntries: 50,
                maxAgeSeconds: 60 * 60 * 24 * 10,
            }),
        ],
    }),
);
