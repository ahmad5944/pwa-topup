const CACHE_NAME = 'topup-pwa-v1';
const STATIC_ASSETS = [
    '/',
    '/dashboard',
    '/catalog',
    '/manifest.webmanifest',
    '/icons/icon.svg',
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(STATIC_ASSETS))
    );
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
        ))
    );
    self.clients.claim();
});

self.addEventListener('fetch', (event) => {
    if (event.request.method !== 'GET') {
        return;
    }

    const url = new URL(event.request.url);
    const wantsInertia = event.request.headers.get('X-Inertia') === 'true';

    if (url.origin !== self.location.origin) {
        return;
    }

    const isCatalogRoute = url.pathname === '/catalog' || url.pathname.startsWith('/catalog/');
    const isDashboardRoute = url.pathname === '/dashboard';
    const isAsset = url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') || url.pathname === '/manifest.webmanifest';

    if (isAsset) {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                const networkFetch = fetch(event.request)
                    .then((response) => {
                        if (response && response.status === 200) {
                            const cloned = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, cloned));
                        }

                        return response;
                    })
                    .catch(() => cachedResponse);

                return cachedResponse || networkFetch;
            })
        );
    }

    if ((isCatalogRoute || isDashboardRoute) && !wantsInertia && event.request.mode === 'navigate') {
        event.respondWith(
            caches.match(event.request).then((cachedResponse) => {
                const networkFetch = fetch(event.request)
                    .then((response) => {
                        if (response && response.status === 200) {
                            const cloned = response.clone();
                            caches.open(CACHE_NAME).then((cache) => cache.put(event.request, cloned));
                        }

                        return response;
                    })
                    .catch(() => cachedResponse);

                return cachedResponse || networkFetch;
            })
        );
    }
});
