import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: 'public',
        emptyOutDir: true,
        rollupOptions: {
            input: {
                'js/tallpress-frontend': 'resources/js/tallpress-frontend.js',
                'js/tallpress-admin': 'resources/js/tallpress-admin.js',
                'css/tallpress-frontend': 'resources/css/tallpress-frontend.css',
                'css/tallpress-admin': 'resources/css/tallpress-admin.css',
            },
            output: {
                entryFileNames: '[name].js',
                assetFileNames: '[name][extname]',
                format: 'es',
            },
        },
    },
});
