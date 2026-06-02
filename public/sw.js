/**
 * ObatKu Service Worker v2.0.0
 *
 * Strategy matrix:
 *  - App shell (CSS/JS/fonts)  → Cache First
 *  - HTML pages                → Network First with offline fallback
 *  - API calls                 → Network Only (with Background Sync queue)
 *  - Static assets (icons/img) → Cache First with 30-day expiry
 *  - Push notifications        → Custom handler
 */

const SW_VERSION     = '__SW_VERSION__';
const CACHE_STATIC   = `obatku-static-v${SW_VERSION}`;
const CACHE_PAGES    = `obatku-pages-v${SW_VERSION}`;
const CACHE_IMAGES   = `obatku-images-v${SW_VERSION}`;
const SYNC_TAG       = 'obatku-background-sync';

/* ─── Assets to Pre-Cache (App Shell) ─────────────────────────────────── */

const PRECACHE_STATIC = [
    '/offline',
    '/manifest.json',
];

const PRECACHE_PAGES = [
    '/dashboard',
    '/medicines',
    '/schedules',
    '/artikel',
    '/ecomed',
];

/* ─── Install: Pre-cache App Shell ────────────────────────────────────── */

self.addEventListener('install', event => {
    console.log(`[SW] Installing ObatKu SW v${SW_VERSION}`);

    event.waitUntil(
        Promise.all([
            caches.open(CACHE_STATIC).then(cache => {
                console.log('[SW] Pre-caching static assets');
                return cache.addAll(PRECACHE_STATIC).catch(err => {
                    console.warn('[SW] Some static assets failed to pre-cache:', err);
                });
            }),
            caches.open(CACHE_PAGES).then(cache => {
                console.log('[SW] Pre-caching pages (best-effort)');
                return Promise.allSettled(
                    PRECACHE_PAGES.map(url =>
                        cache.add(url).catch(() => console.warn(`[SW] Could not pre-cache: ${url}`))
                    )
                );
            }),
        ]).then(() => self.skipWaiting())
    );
});

/* ─── Activate: Clean Old Caches ──────────────────────────────────────── */

self.addEventListener('activate', event => {
    console.log(`[SW] Activating ObatKu SW v${SW_VERSION}`);

    const validCaches = [CACHE_STATIC, CACHE_PAGES, CACHE_IMAGES];

    event.waitUntil(
        caches.keys().then(keys =>
            Promise.all(
                keys
                    .filter(key => !validCaches.includes(key))
                    .map(key => {
                        console.log('[SW] Deleting old cache:', key);
                        return caches.delete(key);
                    })
            )
        ).then(() => self.clients.claim())
    );
});

/* ─── Fetch: Routing Strategy ──────────────────────────────────────────── */

self.addEventListener('fetch', event => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET and cross-origin requests
    if (request.method !== 'GET') return;
    if (url.origin !== location.origin) return;

    // Skip admin routes — always network
    if (url.pathname.startsWith('/admin')) return;

    // Skip Vite HMR
    if (url.pathname.startsWith('/@') || url.pathname.includes('hot-update')) return;

    // API calls — Network Only (mutations handled by Background Sync)
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(networkOnly(request));
        return;
    }

    // Static assets (CSS, JS, fonts, icons, images)
    if (isStaticAsset(url.pathname)) {
        event.respondWith(cacheFirst(request, CACHE_STATIC, { maxAgeSeconds: 30 * 24 * 60 * 60 }));
        return;
    }

    // Images
    if (isImage(url.pathname)) {
        event.respondWith(cacheFirst(request, CACHE_IMAGES, { maxAgeSeconds: 7 * 24 * 60 * 60 }));
        return;
    }

    // HTML pages — Network First with offline fallback
    event.respondWith(networkFirstWithFallback(request));
});

/* ─── Background Sync ──────────────────────────────────────────────────── */

self.addEventListener('sync', event => {
    console.log('[SW] Background sync triggered:', event.tag);

    if (event.tag === SYNC_TAG || event.tag.startsWith('obatku-sync')) {
        event.waitUntil(flushOfflineQueue());
    }
});

/**
 * Replay all queued offline mutations to the server.
 */
async function flushOfflineQueue() {
    const db    = await openIDB();
    const queue = await getAllFromStore(db, 'syncQueue');

    console.log(`[SW] Flushing ${queue.length} queued operations`);

    for (const item of queue) {
        try {
            const response = await fetch('/api/sync', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': item.csrfToken },
                body:    JSON.stringify(item),
            });

            if (response.ok) {
                await deleteFromStore(db, 'syncQueue', item.clientId);
                console.log('[SW] Synced item:', item.clientId);
            } else {
                console.warn('[SW] Server rejected sync item:', item.clientId, response.status);
            }
        } catch (err) {
            console.error('[SW] Sync failed for item:', item.clientId, err);
            // Leave in queue for next sync attempt
        }
    }

    // Notify open clients that sync is complete
    const clients = await self.clients.matchAll({ type: 'window' });
    clients.forEach(client => client.postMessage({ type: 'SYNC_COMPLETE' }));
}

/* ─── Push Notifications ───────────────────────────────────────────────── */

self.addEventListener('push', event => {
    console.log('[SW] Push received');

    let data = { title: 'ObatKu', body: 'Anda punya pemberitahuan baru.' };

    try {
        data = event.data?.json() ?? data;
    } catch {
        data.body = event.data?.text() ?? data.body;
    }

    const options = {
        body:    data.body,
        icon:    data.icon   || '/icons/icon-192x192.png',
        badge:   data.badge  || '/icons/icon-72x72.png',
        image:   data.image  || undefined,
        tag:     data.tag    || 'obatku-notification',
        data:    { url: data.url || '/dashboard', ...data.data },
        actions: data.actions || [
            { action: 'open',   title: 'Buka Aplikasi' },
            { action: 'dismiss', title: 'Tutup' },
        ],
        requireInteraction: data.requireInteraction ?? false,
        silent:             data.silent ?? false,
        vibrate:            data.vibrate ?? [200, 100, 200],
    };

    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

self.addEventListener('notificationclick', event => {
    event.notification.close();

    const targetUrl = event.notification.data?.url || '/dashboard';

    if (event.action === 'dismiss') return;

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then(clients => {
                // Focus existing tab if open
                const existing = clients.find(c => c.url.includes(location.origin));
                if (existing) {
                    return existing.focus().then(c => c.navigate(targetUrl));
                }
                return self.clients.openWindow(targetUrl);
            })
    );
});

self.addEventListener('notificationclose', event => {
    console.log('[SW] Notification closed:', event.notification.tag);
});

/* ─── Caching Strategies ───────────────────────────────────────────────── */

/**
 * Cache First — serves from cache, falls back to network and updates cache.
 */
async function cacheFirst(request, cacheName, { maxAgeSeconds } = {}) {
    const cache    = await caches.open(cacheName);
    const cached   = await cache.match(request);

    if (cached) {
        // Check max-age if specified
        if (maxAgeSeconds) {
            const dateHeader = cached.headers.get('date');
            if (dateHeader) {
                const age = (Date.now() - new Date(dateHeader).getTime()) / 1000;
                if (age > maxAgeSeconds) {
                    return fetchAndCache(request, cache);
                }
            }
        }
        return cached;
    }

    return fetchAndCache(request, cache);
}

/**
 * Network First — tries network, falls back to cache, then offline page.
 */
async function networkFirstWithFallback(request) {
    const cache = await caches.open(CACHE_PAGES);

    try {
        const response = await fetch(request);

        // Only cache successful HTML responses
        if (response.ok && response.headers.get('content-type')?.includes('text/html')) {
            cache.put(request, response.clone());
        }

        return response;
    } catch {
        // Try cached version
        const cached = await cache.match(request);
        if (cached) return cached;

        // Final fallback: offline page
        const offline = await caches.match('/offline');
        return offline || new Response(
            '<html><body><h1>Anda sedang offline</h1><p>Buka ObatKu saat terhubung internet.</p></body></html>',
            { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } }
        );
    }
}

/**
 * Network Only — no caching.
 */
async function networkOnly(request) {
    try {
        return await fetch(request);
    } catch {
        return new Response(
            JSON.stringify({ error: 'offline', message: 'Tidak ada koneksi internet.' }),
            { status: 503, headers: { 'Content-Type': 'application/json' } }
        );
    }
}

async function fetchAndCache(request, cache) {
    try {
        const response = await fetch(request);
        if (response.ok) cache.put(request, response.clone());
        return response;
    } catch {
        return new Response('', { status: 503 });
    }
}

/* ─── Helper Functions ─────────────────────────────────────────────────── */

function isStaticAsset(pathname) {
    return /\.(css|js|woff2?|ttf|eot|svg)$/i.test(pathname)
        || pathname.startsWith('/build/')
        || pathname.startsWith('/icons/');
}

function isImage(pathname) {
    return /\.(png|jpe?g|gif|webp|ico|avif)$/i.test(pathname);
}

/* ─── IndexedDB for Offline Queue ──────────────────────────────────────── */

function openIDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('ObatKuOfflineDB', 1);

        req.onupgradeneeded = e => {
            const db = e.target.result;

            if (!db.objectStoreNames.contains('syncQueue')) {
                const store = db.createObjectStore('syncQueue', { keyPath: 'clientId' });
                store.createIndex('status',      'status',      { unique: false });
                store.createIndex('entityType',  'entityType',  { unique: false });
                store.createIndex('performedAt', 'performedAt', { unique: false });
            }

            if (!db.objectStoreNames.contains('cachedSchedules')) {
                const sched = db.createObjectStore('cachedSchedules', { keyPath: 'id' });
                sched.createIndex('date',   'date',   { unique: false });
                sched.createIndex('userId', 'userId', { unique: false });
            }

            if (!db.objectStoreNames.contains('cachedMedicines')) {
                db.createObjectStore('cachedMedicines', { keyPath: 'id' });
            }
        };

        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

function getAllFromStore(db, storeName) {
    return new Promise((resolve, reject) => {
        const tx   = db.transaction(storeName, 'readonly');
        const req  = tx.objectStore(storeName).getAll();
        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

function deleteFromStore(db, storeName, key) {
    return new Promise((resolve, reject) => {
        const tx  = db.transaction(storeName, 'readwrite');
        const req = tx.objectStore(storeName).delete(key);
        req.onsuccess = () => resolve();
        req.onerror   = e => reject(e.target.error);
    });
}

/* ─── Message Handler (from app) ───────────────────────────────────────── */

self.addEventListener('message', event => {
    const { type, payload } = event.data || {};

    switch (type) {
        case 'SKIP_WAITING':
            self.skipWaiting();
            break;

        case 'CACHE_PAGE':
            if (payload?.url) {
                caches.open(CACHE_PAGES).then(cache => {
                    cache.add(payload.url).catch(() => {});
                });
            }
            break;

        case 'QUEUE_SYNC':
            // Payload already saved to IDB by the client; just register sync.
            if ('serviceWorker' in navigator && 'SyncManager' in window) {
                self.registration.sync.register(SYNC_TAG).catch(console.error);
            }
            break;

        case 'CLEAR_CACHE':
            caches.keys().then(keys => Promise.all(keys.map(k => caches.delete(k))));
            break;
    }
});
