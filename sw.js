/* ================================================================
   ATMABISWAS — Production Service Worker
   ================================================================
   Purpose: show a branded offline page instead of the browser's
   native "can't reach this site" screen — but ONLY for visitors
   whose browser has already installed this worker on a prior
   successful visit. No service worker can help a first-time
   visitor with no connection, since installing it requires a
   network request in the first place.

   Strategy:
     • Navigations (actual page loads)  → NETWORK-FIRST, response
       is NEVER cached (see "why no HTML caching" below), falls
       back to the cached offline.html only if the network fails.
     • Static assets (css/js/images)     → CACHE-FIRST, safe here
       specifically because this site appends a `?v=<filemtime>`
       query string to every asset URL on deploy, so the URL itself
       changes whenever the content does.
     • /backend/ (login, dashboard, career admin) → never touched,
       always goes straight to the network, untouched, uncached.
     • Any *.php request                 → never cached (dynamic,
       session/DB-backed on this site — see below).
     • Cross-origin requests (Font Awesome CDN, Google Tag Manager,
       Leaflet, Google Maps embeds, etc.) → left completely alone.

   Why no PHP/HTML page is ever cached:
   Every .php page on this site is dynamic — session state, live
   DB data, forms with CSRF-style tokens, admin/dashboard content.
   Caching that HTML would risk serving one visitor another
   visitor's data, or a stale form on top of a fresh session. So
   the only thing this worker ever stores for offline use is the
   static offline-fallback shell itself, plus genuinely static,
   fingerprinted assets.
   ================================================================ */

// Bump this string on any change to this file's caching logic —
// activate() below deletes every cache that doesn't match it, so a
// version bump is how you force all clients to drop stale entries.
const CACHE_VERSION = 'v2';
const CACHE_NAME = 'atmabiswas-' + CACHE_VERSION;

const OFFLINE_URL = '/offline.html';

// Deliberately minimal precache list. offline.html is self-contained
// (inline CSS, inline SVG icon, system font stack) so it renders
// correctly even if the logo below somehow isn't in cache — the page
// itself hides a missing logo gracefully (onerror). No font files are
// listed because the site uses system fonts only (Times New Roman),
// and no other CSS/JS is required because offline.html doesn't load any.
const PRECACHE_URLS = [
    OFFLINE_URL,
    '/LOGO/NGO_logo_monogram.png' // also doubles as the favicon
];

// Paths that must never be intercepted at all — always straight to
// the network, no caching, no offline fallback substitution.
//   /backend/ — login, admin dashboard, career/blog admin actions
//   /uploads/ — user-submitted content (CVs in application_cvs/,
//               blog images, notice PDFs) — real people's personal
//               documents; these must never be retained in a
//               visitor's local Cache Storage indefinitely.
const NEVER_INTERCEPT_PREFIXES = ['/backend/', '/uploads/'];

// Extensions treated as safe, static, cache-first assets.
const STATIC_ASSET_RE = /\.(css|js|png|jpe?g|gif|svg|webp|woff2?|ttf|otf|ico)$/i;

/* ----------------------------------------------------------------
   INSTALL
   Precache the offline shell, then skipWaiting() so a newly
   installed/updated worker activates immediately instead of
   waiting for every open tab of the old version to close.
---------------------------------------------------------------- */
self.addEventListener('install', function (event) {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(function (cache) { return cache.addAll(PRECACHE_URLS); })
            .then(function () { return self.skipWaiting(); })
    );
});

/* ----------------------------------------------------------------
   ACTIVATE
   Delete any cache left over from a previous CACHE_VERSION, then
   clients.claim() so already-open tabs are controlled by this
   worker right away rather than only on their next full reload.
---------------------------------------------------------------- */
self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys()
            .then(function (keys) {
                return Promise.all(
                    keys
                        .filter(function (key) { return key !== CACHE_NAME; })
                        .map(function (key) { return caches.delete(key); })
                );
            })
            .then(function () { return self.clients.claim(); })
    );
});

/* ----------------------------------------------------------------
   FETCH — routing
---------------------------------------------------------------- */
self.addEventListener('fetch', function (event) {
    var request = event.request;
    var url = new URL(request.url);

    // Only ever handle same-origin requests. Leave every third-party
    // request (Font Awesome CDN, Google Tag Manager, Leaflet, Google
    // Maps embeds, axios CDN, etc.) completely untouched.
    if (url.origin !== self.location.origin) return;

    // Login / admin dashboard — never intercepted, never cached.
    var isNeverIntercept = NEVER_INTERCEPT_PREFIXES.some(function (prefix) {
        return url.pathname.indexOf(prefix) === 0;
    });
    if (isNeverIntercept) return;

    // Full-page navigation (address bar, link click, refresh):
    // network-first, offline.html as the only fallback.
    if (request.mode === 'navigate') {
        event.respondWith(networkFirstNavigation(request));
        return;
    }

    // Any other *.php request (AJAX/fetch calls such as
    // Action/get_branches.php) — dynamic, always network, never cached.
    if (url.pathname.toLowerCase().endsWith('.php')) return;

    // Genuinely static, per-deploy-fingerprinted assets: cache-first.
    if (STATIC_ASSET_RE.test(url.pathname)) {
        event.respondWith(cacheFirstStatic(request));
        return;
    }

    // Everything else (e.g. .json data files) — default browser behavior.
});

/**
 * Network-first for page navigations. The response is intentionally
 * NEVER written to cache (see file header for why) — this function
 * only ever reads from cache when the network request itself fails.
 */
function networkFirstNavigation(request) {
    return fetch(request).catch(function () {
        return caches.match(OFFLINE_URL);
    });
}

/**
 * Cache-first for static assets. Checks cache first for speed; on a
 * cache miss, fetches from the network and stores a copy for next
 * time. If both cache and network miss (offline + never seen this
 * asset before), the returned promise rejects naturally — the
 * correct, honest outcome for a resource we genuinely don't have.
 */
function cacheFirstStatic(request) {
    return caches.open(CACHE_NAME).then(function (cache) {
        return cache.match(request).then(function (cached) {
            if (cached) return cached;

            return fetch(request).then(function (response) {
                if (response && response.status === 200) {
                    cache.put(request, response.clone());
                }
                return response;
            });
        });
    });
}
