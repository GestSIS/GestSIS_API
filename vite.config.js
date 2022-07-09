import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

export default defineConfig({
    server: {
        host: true,
        hmr: {
            host: '127.0.0.1'
        }
    },
    plugins: [
        laravel([
            'resources/sass/app.scss',
        ]),
    ],
    resolve: {
        alias: {
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
        }
    },
});