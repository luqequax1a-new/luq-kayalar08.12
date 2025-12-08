const staticCacheName = "pwa-v" + new Date().getTime();

/*
|--------------------------------------------------------------------------
| Cache On Install
|--------------------------------------------------------------------------
*/
self.addEventListener("install", (event) => {
    this.skipWaiting();

    event.waitUntil(
        caches.open(staticCacheName).then((cache) => {
            fetch("/build/manifest.json")
                .then((response) => {
                    return response.json();
                })
                .then((assets) => {
                    const filesToCache = [
                        "/offline",
                        "/build/" +
                            assets[
                                "modules/Storefront/Resources/assets/public/sass/app.scss"
                            ].file,
                        "/build/" +
                            assets[
                                "modules/Storefront/Resources/assets/public/js/app.js"
                            ].file,
                        "/build/" +
                            assets[
                                "modules/Storefront/Resources/assets/public/js/main.js"
                            ].file,
                        "/pwa/icons/48x48.png",
                        "/pwa/icons/72x72.png",
                        "/pwa/icons/96x96.png",
                        "/pwa/icons/128x128.png",
                        "/pwa/icons/144x144.png",
                        "/pwa/icons/152x152.png",
                        "/pwa/icons/192x192.png",
                        "/pwa/icons/384x384.png",
                        "/pwa/icons/512x512.png",
                    ];

                    return cache.addAll(filesToCache);
                });
        })
    );
});

/*
|--------------------------------------------------------------------------
| Clear Cache On Activate
|--------------------------------------------------------------------------
*/
self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames
                    .filter((cacheName) => cacheName.startsWith("pwa-"))
                    .filter((cacheName) => cacheName !== staticCacheName)
                    .map((cacheName) => caches.delete(cacheName))
            );
        })
    );
});

/*
|--------------------------------------------------------------------------
| Serve From Cache
|--------------------------------------------------------------------------
*/
self.addEventListener("fetch", (event) => {
    const req = event.request;

    // Only use offline fallback for navigation (HTML page requests)
    if (req.mode === "navigate") {
        event.respondWith(
            fetch(req).catch(() => caches.match("/offline"))
        );
        return;
    }

    // Skip cross-origin requests entirely
    try {
        const reqUrl = new URL(req.url);
        const swUrl = new URL(self.location.origin);
        if (reqUrl.origin !== swUrl.origin) {
            event.respondWith(fetch(req).catch(() => new Response(null, { status: 502 })));
            return;
        }
    } catch (_) {}

    // For same-origin requests, try cache then network with safe fallback
    event.respondWith(
        caches.match(req).then((response) => {
            if (response) return response;
            return fetch(req).catch(() => new Response(null, { status: 502 }));
        })
    );
});

const pwaVersion = 1765197462;
