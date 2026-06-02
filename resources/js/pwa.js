/**
 * ObatKu PWA — Client-Side Registration & Push Manager
 * resources/js/pwa.js
 *
 * Handles:
 *  1. Service Worker registration with update prompt
 *  2. Web Push subscription (VAPID)
 *  3. Offline queue manager (IndexedDB)
 *  4. Install prompt (A2HS)
 *  5. Online/offline status banner
 *  6. Background Sync trigger
 */

/* ─────────────────────────────────────────────────────────────────────────
 | 1. SERVICE WORKER REGISTRATION
 |──────────────────────────────────────────────────────────────────────── */

let swRegistration = null;

export async function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
        console.warn('[PWA] Service Worker not supported.');
        return null;
    }

    try {
        swRegistration = await navigator.serviceWorker.register('/sw.js', {
            scope:         '/',
            updateViaCache: 'none',
        });

        console.log('[PWA] Service Worker registered:', swRegistration.scope);

        // Detect new SW waiting to activate → show update toast
        swRegistration.addEventListener('updatefound', () => {
            const newWorker = swRegistration.installing;
            newWorker?.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                    showUpdateToast();
                }
            });
        });

        // Receive messages from SW
        navigator.serviceWorker.addEventListener('message', handleSwMessage);

        return swRegistration;
    } catch (err) {
        console.error('[PWA] SW registration failed:', err);
        return null;
    }
}

function showUpdateToast() {
    const toast = document.createElement('div');
    toast.id    = 'pwa-update-toast';
    toast.style.cssText = `
        position: fixed; bottom: 1.5rem; left: 50%; transform: translateX(-50%);
        background: #042C53; color: #fff; padding: 0.875rem 1.25rem;
        border-radius: 0.875rem; box-shadow: 0 8px 30px rgba(0,0,0,.2);
        display: flex; align-items: center; gap: 1rem; z-index: 9999;
        font-family: Inter, sans-serif; font-size: 0.9375rem; min-width: 300px;
        animation: slideUp 0.3s ease-out;
    `;
    toast.innerHTML = `
        <span>🔄 Pembaruan ObatKu tersedia!</span>
        <button id="pwa-update-btn" style="
            background: #185FA5; color: #fff; border: none; border-radius: 0.5rem;
            padding: 0.375rem 0.875rem; font-size: 0.875rem; font-weight: 600;
            cursor: pointer; white-space: nowrap;">
            Perbarui Sekarang
        </button>
    `;
    document.body.appendChild(toast);

    document.getElementById('pwa-update-btn')?.addEventListener('click', () => {
        swRegistration?.waiting?.postMessage({ type: 'SKIP_WAITING' });
        window.location.reload();
    });
}

function handleSwMessage(event) {
    const { type } = event.data || {};

    if (type === 'SYNC_COMPLETE') {
        console.log('[PWA] Background sync complete — refreshing queue UI');
        document.dispatchEvent(new CustomEvent('obatku:sync-complete'));
    }
}

/* ─────────────────────────────────────────────────────────────────────────
 | 2. PUSH NOTIFICATIONS
 |──────────────────────────────────────────────────────────────────────── */

const VAPID_PUBLIC_KEY = document.querySelector('meta[name="vapid-public-key"]')?.content ?? '';

export async function subscribeToPush() {
    if (!swRegistration) {
        console.warn('[PWA] SW not registered — cannot subscribe to push.');
        return false;
    }

    if (!('PushManager' in window)) {
        console.warn('[PWA] Push notifications not supported.');
        return false;
    }

    const permission = await Notification.requestPermission();
    if (permission !== 'granted') {
        console.warn('[PWA] Push permission denied.');
        return false;
    }

    try {
        const subscription = await swRegistration.pushManager.subscribe({
            userVisibleOnly:      true,
            applicationServerKey: urlBase64ToUint8Array(VAPID_PUBLIC_KEY),
        });

        // Send subscription to Laravel backend
        const response = await fetch('/api/pwa/push-subscribe', {
            method:  'POST',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  getCsrfToken(),
                'Accept':        'application/json',
            },
            body: JSON.stringify({
                endpoint:   subscription.endpoint,
                p256dh_key: arrayBufferToBase64(subscription.getKey('p256dh')),
                auth_token: arrayBufferToBase64(subscription.getKey('auth')),
                device_name: getDeviceName(),
                user_agent:  navigator.userAgent,
            }),
        });

        if (response.ok) {
            console.log('[PWA] Push subscription saved.');
            return true;
        } else {
            console.error('[PWA] Failed to save push subscription:', await response.text());
            return false;
        }
    } catch (err) {
        console.error('[PWA] Push subscription error:', err);
        return false;
    }
}

export async function unsubscribeFromPush() {
    const subscription = await swRegistration?.pushManager.getSubscription();
    if (!subscription) return true;

    const success = await subscription.unsubscribe();
    if (success) {
        await fetch('/api/pwa/push-unsubscribe', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': getCsrfToken() },
            body:    JSON.stringify({ endpoint: subscription.endpoint }),
        });
        console.log('[PWA] Push unsubscribed.');
    }
    return success;
}

export async function getPushStatus() {
    const subscription = await swRegistration?.pushManager.getSubscription();
    return {
        subscribed:  !!subscription,
        permission:  Notification.permission,
        supported:   'PushManager' in window,
    };
}

/* ─────────────────────────────────────────────────────────────────────────
 | 3. OFFLINE QUEUE (IndexedDB)
 |──────────────────────────────────────────────────────────────────────── */

let _idb = null;

async function getIDB() {
    if (_idb) return _idb;
    _idb = await openIDB();
    return _idb;
}

function openIDB() {
    return new Promise((resolve, reject) => {
        const req = indexedDB.open('ObatKuOfflineDB', 1);

        req.onupgradeneeded = e => {
            const db = e.target.result;
            if (!db.objectStoreNames.contains('syncQueue')) {
                const store = db.createObjectStore('syncQueue', { keyPath: 'clientId' });
                store.createIndex('status', 'status', { unique: false });
                store.createIndex('entityType', 'entityType', { unique: false });
            }
            if (!db.objectStoreNames.contains('cachedSchedules')) {
                const sched = db.createObjectStore('cachedSchedules', { keyPath: 'id' });
                sched.createIndex('date', 'date', { unique: false });
            }
            if (!db.objectStoreNames.contains('cachedMedicines')) {
                db.createObjectStore('cachedMedicines', { keyPath: 'id' });
            }
        };

        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

/**
 * Queue an offline mutation to be replayed when online.
 * entityType: 'consumption' | 'schedule_log' | 'waste_report'
 * action:     'create' | 'update' | 'delete'
 */
export async function queueOfflineAction(entityType, action, payload) {
    const db       = await getIDB();
    const clientId = crypto.randomUUID();
    const item     = {
        clientId,
        entityType,
        action,
        payload,
        status:      'pending',
        attempts:    0,
        performedAt: new Date().toISOString(),
        csrfToken:   getCsrfToken(),
    };

    await new Promise((resolve, reject) => {
        const tx  = db.transaction('syncQueue', 'readwrite');
        const req = tx.objectStore('syncQueue').add(item);
        req.onsuccess = () => resolve();
        req.onerror   = e => reject(e.target.error);
    });

    console.log('[PWA] Queued offline action:', entityType, action, clientId);

    // Register background sync
    if (swRegistration && 'sync' in swRegistration) {
        await swRegistration.sync.register('obatku-background-sync').catch(() => {});
    }

    return clientId;
}

/** Get all pending items in the sync queue. */
export async function getQueuedItems() {
    const db = await getIDB();
    return new Promise((resolve, reject) => {
        const tx  = db.transaction('syncQueue', 'readonly');
        const req = tx.objectStore('syncQueue').getAll();
        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

/** Cache today's schedules for offline access. */
export async function cacheSchedules(schedules) {
    const db = await getIDB();
    const tx = db.transaction('cachedSchedules', 'readwrite');
    const store = tx.objectStore('cachedSchedules');

    for (const schedule of schedules) {
        store.put({ ...schedule, cachedAt: new Date().toISOString() });
    }

    return new Promise((resolve, reject) => {
        tx.oncomplete = () => resolve();
        tx.onerror    = e => reject(e.target.error);
    });
}

/** Get cached schedules for a specific date. */
export async function getCachedSchedules(date) {
    const db = await getIDB();
    return new Promise((resolve, reject) => {
        const tx    = db.transaction('cachedSchedules', 'readonly');
        const index = tx.objectStore('cachedSchedules').index('date');
        const req   = index.getAll(IDBKeyRange.only(date));
        req.onsuccess = e => resolve(e.target.result);
        req.onerror   = e => reject(e.target.error);
    });
}

/* ─────────────────────────────────────────────────────────────────────────
 | 4. INSTALL PROMPT (Add to Home Screen)
 |──────────────────────────────────────────────────────────────────────── */

let installPromptEvent = null;

window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    installPromptEvent = e;
    showInstallBanner();
});

window.addEventListener('appinstalled', () => {
    installPromptEvent = null;
    hideInstallBanner();
    console.log('[PWA] ObatKu installed!');
});

function showInstallBanner() {
    if (document.getElementById('pwa-install-banner')) return;

    // Only show if not already in standalone mode
    if (window.matchMedia('(display-mode: standalone)').matches) return;
    if (window.navigator.standalone) return;

    const banner = document.createElement('div');
    banner.id = 'pwa-install-banner';
    banner.style.cssText = `
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 9998;
        background: #042C53; padding: 1rem 1.25rem;
        display: flex; align-items: center; gap: 1rem;
        box-shadow: 0 -4px 20px rgba(0,0,0,.15);
        font-family: Inter, sans-serif;
        animation: slideUp 0.35s ease-out;
    `;
    banner.innerHTML = `
        <style>@keyframes slideUp{from{transform:translateY(100%)}to{transform:translateY(0)}}</style>
        <div style="width:40px;height:40px;background:#185FA5;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="22" height="22" fill="none" stroke="#fff" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
        </div>
        <div style="flex:1;">
            <p style="color:#fff;font-weight:600;font-size:0.9375rem;margin-bottom:2px;">Pasang ObatKu</p>
            <p style="color:rgba(255,255,255,.65);font-size:0.8125rem;">Akses cepat & dukungan offline penuh</p>
        </div>
        <button id="pwa-install-yes" style="background:#185FA5;color:#fff;border:none;border-radius:0.625rem;padding:0.5rem 1rem;font-weight:600;font-size:0.875rem;cursor:pointer;">Pasang</button>
        <button id="pwa-install-no"  style="background:transparent;color:rgba(255,255,255,.5);border:none;font-size:1.25rem;cursor:pointer;line-height:1;padding:0.25rem;">✕</button>
    `;
    document.body.appendChild(banner);

    document.getElementById('pwa-install-yes')?.addEventListener('click', async () => {
        if (!installPromptEvent) return;
        installPromptEvent.prompt();
        const { outcome } = await installPromptEvent.userChoice;
        console.log('[PWA] Install outcome:', outcome);
        installPromptEvent = null;
        hideInstallBanner();
    });

    document.getElementById('pwa-install-no')?.addEventListener('click', hideInstallBanner);
}

function hideInstallBanner() {
    document.getElementById('pwa-install-banner')?.remove();
}

/* ─────────────────────────────────────────────────────────────────────────
 | 5. ONLINE / OFFLINE STATUS BANNER
 |──────────────────────────────────────────────────────────────────────── */

function showOfflineBanner() {
    if (document.getElementById('pwa-offline-banner')) return;

    const banner = document.createElement('div');
    banner.id = 'pwa-offline-banner';
    banner.style.cssText = `
        position: fixed; top: 0; left: 0; right: 0; z-index: 9997;
        background: #EF9F27; color: #fff; text-align: center;
        padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 600;
        font-family: Inter, sans-serif;
    `;
    banner.textContent = '⚡ Anda sedang offline — tindakan akan disinkronkan saat online kembali';
    document.body.prepend(banner);
}

function hideOfflineBanner() {
    const banner = document.getElementById('pwa-offline-banner');
    if (banner) {
        banner.style.background = '#1D9E75';
        banner.textContent = '✅ Koneksi kembali! Sinkronisasi data...';
        setTimeout(() => banner.remove(), 3000);
    }
}

window.addEventListener('online',  () => { hideOfflineBanner(); triggerSync(); });
window.addEventListener('offline', showOfflineBanner);

if (!navigator.onLine) showOfflineBanner();

async function triggerSync() {
    if (swRegistration && 'sync' in swRegistration) {
        await swRegistration.sync.register('obatku-background-sync').catch(() => {});
    }
}

/* ─────────────────────────────────────────────────────────────────────────
 | 6. UTILITIES
 |──────────────────────────────────────────────────────────────────────── */

function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64  = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw     = atob(base64);
    return Uint8Array.from([...raw].map(c => c.charCodeAt(0)));
}

function arrayBufferToBase64(buffer) {
    return btoa(String.fromCharCode(...new Uint8Array(buffer)));
}

function getDeviceName() {
    const ua = navigator.userAgent;
    if (/iPhone/.test(ua))   return 'iPhone';
    if (/iPad/.test(ua))     return 'iPad';
    if (/Android/.test(ua))  return 'Android';
    if (/Windows/.test(ua))  return 'Windows PC';
    if (/Mac/.test(ua))      return 'Mac';
    if (/Linux/.test(ua))    return 'Linux';
    return 'Browser';
}

/* ─────────────────────────────────────────────────────────────────────────
 | 7. AUTO-INIT
 |──────────────────────────────────────────────────────────────────────── */

document.addEventListener('DOMContentLoaded', async () => {
    await registerServiceWorker();

    // Cache pages proactively in background
    if (navigator.onLine && swRegistration) {
        const pagesToCache = ['/dashboard', '/schedules', '/ecomed', '/artikel'];
        for (const page of pagesToCache) {
            swRegistration.active?.postMessage({ type: 'CACHE_PAGE', payload: { url: page } });
        }
    }
});

// Export for use by individual Blade pages
window.ObatKuPWA = {
    subscribeToPush,
    unsubscribeFromPush,
    getPushStatus,
    queueOfflineAction,
    getQueuedItems,
    cacheSchedules,
    getCachedSchedules,
};
