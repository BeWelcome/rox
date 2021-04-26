import {precacheAndRoute} from 'workbox-precaching';
import {
    NetworkFirst,
    StaleWhileRevalidate,
    CacheFirst,
} from 'workbox-strategies';
import { CacheableResponsePlugin } from 'workbox-cacheable-response';
import {registerRoute} from 'workbox-routing';
import { ExpirationPlugin } from 'workbox-expiration';

precacheAndRoute(self.__WB_MANIFEST);

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

// Cache conversations for 30 days
registerRoute(
    ({url}) =>
        url.pathname.startsWith('/message') ||
        url.pathname.startsWith('/request'),
    new CacheFirst({
        cacheName: 'conversations',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [200],
            }),
            new ExpirationPlugin({
                maxEntries: 100,
                maxAgeSeconds: 60 * 60 * 24 * 30,
            }),
        ],
    }),
);

// Cache members profiles a Cache First strategy
registerRoute(
    ({url}) => url.pathname.startsWith('/members'),
    new CacheFirst({
        cacheName: 'members',
        plugins: [
            new CacheableResponsePlugin({
                statuses: [200],
            }),
            new ExpirationPlugin({
                maxEntries: 50,
                maxAgeSeconds: 60 * 60 * 24 * 30, // 30 Days
            }),
        ],
    }),
);
