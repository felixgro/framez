import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

const DEV_ORIGIN = "http://wp.test";

export default defineConfig(() => ({
    base: "",
    build: {
        emptyOutDir: true,
        manifest: true,
        outDir: "build",
        assetsDir: "assets",
        sourcemap: true
    },
    server: {
        port: 3000,
        cors: {
            origin: DEV_ORIGIN,
            credentials: true,
        },
    },
    plugins: [
        laravel({
            publicDirectory: "build",
            refresh: ["*/**.php"],
            input: [
                "resources/scripts/main.js",
                "resources/styles/main.scss",
                "resources/styles/admin.scss",
                'resources/scripts/admin.js',
            ],
        }),
    ],
    resolve: {
        alias: [
            {
                find: /~(.+)/,
                replacement: process.cwd() + "/node_modules/$1",
            },
        ],
    },
    css: {
        preprocessorOptions: {
            scss: {
                api: "modern-compiler", // or "modern"
            },
        },
    },
}));
