import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            refresh: true,
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
            ],
        }),
        inertia(),
        react({
            babel: {
                plugins: ['babel-plugin-react-compiler'],
            },
        }),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
    server: {
        // Bind em todas as interfaces do container para a porta mapeada funcionar.
        host: '0.0.0.0',
        port: 5173,
        // URL que vai para o public/hot e o cliente HMR: localhost (mapeado pelo
        // Docker), evitando o [::1] IPv6 que não é exposto.
        hmr: {
            host: 'localhost',
        },
        // inotify não propaga em bind mounts do Docker; polling garante o refresh.
        watch: {
            usePolling: true,
        },
    },
});
