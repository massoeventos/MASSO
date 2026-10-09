import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

// Build de las piezas en Vue 3 (checkout público y editor de tickets del
// panel). El resto del sitio y del panel siguen con laravel-mix
// (webpack.mix.js / mix-manifest.json). Las vistas las cargan con
// @include('common.vite-entry').
// buildDirectory y hotFile propios: mix() también lee public/hot, así que
// usar el default rompería los assets del panel mientras corre `vite` en dev.
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/checkout/app.js',
                'resources/js/admin/events.js',
            ],
            buildDirectory: 'build-vue',
            hotFile: 'public/build-vue/hot',
            refresh: false,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
});
