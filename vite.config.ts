import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import { defineConfig } from 'vite';

export default defineConfig(({ command }) => ({
    // The production image doesn't ship node_modules, so the SSR bundle must
    // be fully self-contained (no externalized runtime imports) once built.
    ssr:
        command === 'serve'
            ? {
                  external: [
                      'react',
                      'react-dom',
                      'react-dom/server',
                      'react/jsx-runtime',
                      'react/jsx-dev-runtime',
                  ],
              }
            : {
                  noExternal: true,
              },

    optimizeDeps: {
        include: [
            'react',
            'react-dom',
            'react-dom/client',
            'react-dom/server',
            'react/jsx-runtime',
            'react/jsx-dev-runtime',
        ],
    },

    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
            fonts: [
                bunny('Space Grotesk', {
                    weights: [400, 500, 600, 700],
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
}));
