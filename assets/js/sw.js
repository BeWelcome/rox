import {precacheAndRoute} from 'workbox-precaching';
import {
    NetworkFirst,
    StaleWhileRevalidate,
    CacheFirst,
} from 'workbox-strategies';
import { CacheableResponsePlugin } from 'workbox-cacheable-response';
import {registerRoute} from 'workbox-routing';
import { ExpirationPlugin } from 'workbox-expiration';
import {Workbox} from 'workbox-window';

if ('serviceWorker' in navigator) {
    const wb = new Workbox('/sw.js');

    wb.register();
}

precacheAndRoute(self.__WB_MANIFEST);

addEventListener('install', event => {
    skipWaiting();
});

addEventListener("message", event => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        skipWaiting();
    }
});

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
    new StaleWhileRevalidate({
        cacheName: 'assets',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [200],
            }),
        ],
    }),
);

registerRoute(
    new RegExp('/message/.*') || new RegExp('/request/.*') || new RegExp('/invitation/.*'),
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
    new StaleWhileRevalidate({
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
