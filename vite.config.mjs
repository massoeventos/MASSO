import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

// Build solo del checkout público (Vue 3). El resto del sitio y el panel
// siguen con laravel-mix (webpack.mix.js / mix-manifest.json).
// buildDirectory y hotFile propios: mix() también lee public/hot, así que
// usar el default rompería los assets del panel mientras corre `vite` en dev.
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/checkout/app.js'],
            buildDirectory: 'build-checkout',
            hotFile: 'public/build-checkout/hot',
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
