import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                bunny('Montserrat', {
                    weights: [400, 500, 600],
                    subsets: ['latin', 'vietnamese'],
                    display: 'swap',
                }),
                bunny('Cormorant Garamond', {
                    weights: [400, 500, 600, 700],
                    subsets: ['latin', 'vietnamese'],
                    display: 'swap',
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
