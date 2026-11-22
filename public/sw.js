/* ============================================================================
   Traffic Citation & Enforcement System — Service Worker
   ----------------------------------------------------------------------------
   What is cached:
     • The app's own static assets (CSS, JS, fonts, icons, logo) so repeat
       visits and the installed app open quickly.
     • A self-contained offline page used when a screen cannot be reached.

   What is NOT cached:
     • HTML screens and anything under uploads/. Citation, payment and driver
       records are personal data; keeping rendered pages in the Cache API
       would leave them readable on a shared device after sign-out.

   Bump VERSION whenever the caching rules or the precache list change, so
   every installed device drops the old cache on the next visit.
   ========================================================================== */

const VERSION = 'v2';
const STATIC_CACHE = `traffic-static-${VERSION}`;
const RUNTIME_CACHE = `traffic-runtime-${VERSION}`;

const BASE_URL = new URL('./', self.location.href);
const OFFLINE_URL = new URL('offline.html', BASE_URL).href;

const PRECACHE_PATHS = [
    'offline.html',
    'manifest.webmanifest',
    'css/pwa.css',
    'js/pwa.js',
    'pwa-icons/icon-192.png',
    'pwa-icons/icon-512.png',
    'pwa-icons/maskable-192.png',
    'pwa-icons/maskable-512.png',
    'pwa-icons/apple-touch-icon.png',
    'img/pic-1.png',
    'vendor/bootstrap/bootstrap.min.css',
    'vendor/bootstrap/bootstrap.bundle.min.js',
    'vendor/bootstrap-icons/bootstrap-icons.css',
    'vendor/bootstrap-icons/fonts/bootstrap-icons.woff2',
    'vendor/inter/inter.css',
    'vendor/inter/inter-400.woff2',
    'vendor/inter/inter-500.woff2',
    'vendor/inter/inter-600.woff2',
    'vendor/inter/inter-700.woff2',
    'vendor/inter/inter-800.woff2',
    'vendor/sweetalert2/sweetalert2.min.css',
    'vendor/sweetalert2/sweetalert2.all.min.js',
];

const PRECACHE_URLS = PRECACHE_PATHS.map((path) => new URL(path, BASE_URL).href);

/* ── Install ──────────────────────────────────────────────────────────────── */
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => Promise.all(
            // One missing file must not abort the whole install.
            PRECACHE_URLS.map((url) => cache.add(url).catch(() => null)),
        )).then(() => self.skipWaiting()),
    );
});

/* ── Activate ─────────────────────────────────────────────────────────────── */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys
                .filter((key) => key.startsWith('traffic-') && key !== STATIC_CACHE && key !== RUNTIME_CACHE)
                .map((key) => caches.delete(key)),
        )).then(() => self.clients.claim()),
    );
});

/* ── Messages ─────────────────────────────────────────────────────────────── */
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});

/* ── Fetch ────────────────────────────────────────────────────────────────── */
self.addEventListener('fetch', (event) => {
    const request = event.request;

    // Only GET requests can be cached, and only same-origin traffic is ours.
    if (request.method !== 'GET') {
        return;
    }

    let url;
    try {
        url = new URL(request.url);
    } catch (error) {
        return;
    }

    if (url.origin !== self.location.origin) {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(networkFirstNavigation(request));
        return;
    }

    if (isStaticAsset(url)) {
        event.respondWith(cacheFirst(request));
    }
});

/* Screens always come from the network. A failed navigation gets the offline
   page, never a cached copy of a signed-in screen. */
async function networkFirstNavigation(request) {
    try {
        return await fetch(request);
    } catch (error) {
        const offline = await caches.match(OFFLINE_URL);
        if (offline) {
            return offline;
        }

        return new Response('You are offline. Reconnect and try again.', {
            status: 503,
            headers: { 'Content-Type': 'text/plain; charset=utf-8' },
        });
    }
}

/* Static assets come from the cache instantly, while the network copy
   refreshes the cache in the background (stale-while-revalidate). */
async function cacheFirst(request) {
    const cached = await caches.match(request);

    const network = fetch(request).then((response) => {
        if (response && response.ok && isCacheableResponse(response)) {
            const copy = response.clone();
            caches.open(RUNTIME_CACHE)
                .then((cache) => cache.put(request, copy))
                .catch(() => null);
        }

        return response;
    }).catch(() => cached);

    return cached || network;
}

function isCacheableResponse(response) {
    if (response.status !== 200 || response.type !== 'basic') {
        return false;
    }

    const cacheControl = (response.headers.get('Cache-Control') || '').toLowerCase();

    return !cacheControl.includes('no-store') && !cacheControl.includes('private');
}

const CACHEABLE_PREFIXES = ['css/', 'js/', 'pwa-icons/', 'img/', 'vendor/'];
const CACHEABLE_ROOT_FILES = ['favicon.ico', 'manifest.webmanifest'];
const CACHEABLE_EXTENSION = /\.(?:css|js|mjs|png|jpe?g|gif|svg|webp|avif|ico|woff2?|ttf|eot|webmanifest|json)$/i;

function isStaticAsset(url) {
    const scopePath = BASE_URL.pathname;

    if (!url.pathname.startsWith(scopePath)) {
        return false;
    }

    const relative = url.pathname.slice(scopePath.length);

    if (relative.startsWith('uploads/')) {
        return false;
    }

    if (!CACHEABLE_EXTENSION.test(relative)) {
        return false;
    }

    if (CACHEABLE_ROOT_FILES.includes(relative)) {
        return true;
    }

    return CACHEABLE_PREFIXES.some((prefix) => relative.startsWith(prefix));
}
