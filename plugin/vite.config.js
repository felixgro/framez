import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

const DEV_ORIGIN = "http://gum.test";

export default defineConfig(() => ({
    base: "",
    build: {
        emptyOutDir: true,
        manifest: true,
        outDir: "build",
        assetsDir: "assets",
        sourcemap: false
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
            input: ["resources/scripts/main.js", "resources/styles/main.scss"],
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
