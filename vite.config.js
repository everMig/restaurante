import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    css: {
        preprocessorOptions: {
            scss: {
                silenceDeprecations: ['color-functions', 'global-builtin', 'import', 'math-bounded', 'if-function'],
            },
        },
    },
    plugins: [
        react(),
        tailwindcss(),
        laravel({
            input: [
                'resources/css/tailwind.css', 
                'resources/css/app.scss', 
                'resources/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
