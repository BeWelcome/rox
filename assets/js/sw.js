import {precacheAndRoute} from 'workbox-precaching';
import {NetworkFirst} from 'workbox-strategies';
import {registerRoute} from 'workbox-routing';

precacheAndRoute(self.__WB_MANIFEST);

addEventListener("message", event => {
    if (event.data && event.data.type === "SKIP_WAITING") {
        skipWaiting();
    }
});

registerRoute(
    ({url}) => url.pathname === '/',
    new NetworkFirst()
);

registerRoute(
    ({url}) => url.pathname.startsWith('/message'),
    new NetworkFirst()
);

registerRoute(
    ({url}) => url.pathname.startsWith('/request'),
    new NetworkFirst()
);

registerRoute(
    ({url}) => url.pathname.startsWith('/members'),
    new NetworkFirst()
);

