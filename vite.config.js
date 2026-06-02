import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { copyFileSync, existsSync } from 'fs';
import { resolve } from 'path';

/**
 * ObatKu Vite Configuration
 *
 * Service Worker notes:
 *  - sw.js MUST be served from the root scope (/sw.js), NOT bundled.
 *  - We copy public/sw.js to the build output via a custom Vite plugin
 *    so it is included in production deployments without being transformed.
 *  - The SW is registered at /sw.js directly from the public directory
 *    during development (Laravel serves it via PwaController).
 */
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),

        tailwindcss(),

        // ── Service Worker: copy-only plugin ──────────────────────────────
        // Copies public/sw.js to public/build/sw.js so it is available
        // after `npm run build` without Vite transforming/hashing it.
        {
            name: 'obatku-copy-sw',
            apply: 'build',
            closeBundle() {
                const src  = resolve(__dirname, 'public/sw.js');
                const dest = resolve(__dirname, 'public/build/sw.js');
                if (existsSync(src)) {
                    copyFileSync(src, dest);
                    console.log('[ObatKu] ✅ Copied sw.js to public/build/sw.js');
                }
            },
        },

        // ── Offline page: copy-only plugin ───────────────────────────────
        {
            name: 'obatku-copy-offline',
            apply: 'build',
            closeBundle() {
                const src  = resolve(__dirname, 'public/offline.html');
                const dest = resolve(__dirname, 'public/build/offline.html');
                if (existsSync(src)) {
                    copyFileSync(src, dest);
                    console.log('[ObatKu] ✅ Copied offline.html to public/build/');
                }
            },
        },

        // ── Laravel Manifest fix: copy .vite/manifest.json to build root ──
        {
            name: 'obatku-copy-manifest',
            apply: 'build',
            closeBundle() {
                const src  = resolve(__dirname, 'public/build/.vite/manifest.json');
                const dest = resolve(__dirname, 'public/build/manifest.json');
                if (existsSync(src)) {
                    copyFileSync(src, dest);
                    console.log('[ObatKu] ✅ Copied manifest.json to public/build/');
                }
            },
        },
    ],

    server: {
        watch: {
            // Exclude large generated directories from HMR watcher
            ignored: [
                '**/storage/framework/views/**',
                '**/public/icons/**',
                '**/public/build/**',
            ],
        },
    },

    build: {
        // Generate a manifest for cache-busting (used by Laravel Vite plugin)
        manifest: true,
        // Do NOT inline assets — keep them as separate files for caching
        assetsInlineLimit: 0,
        rollupOptions: {
            output: {
                // Consistent chunk naming for service worker cache keys
                entryFileNames:   'assets/[name]-[hash].js',
                chunkFileNames:   'assets/[name]-[hash].js',
                assetFileNames:   'assets/[name]-[hash][extname]',
            },
        },
    },
});
