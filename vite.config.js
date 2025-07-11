import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js", "resources/css/auth.css", "resources/js/auth.js"],
            refresh: true,
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
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
    // Add server configuration with proxy
    server: {
        proxy: {
            // Proxy /api requests to the Laravel backend server
            '/api': {
                target: 'http://localhost:8000', // Assuming Laravel backend runs on port 8000
                changeOrigin: true,
                // secure: false, // Uncomment if backend uses self-signed certificate
            },
        },
        // Ensure Vite listens on all interfaces if needed for external access
        // host: '0.0.0.0', 
    },
});

