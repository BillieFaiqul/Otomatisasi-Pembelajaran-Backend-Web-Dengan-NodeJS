import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

const host = "localhost";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: {
        // host,
        // hmr: { host },
        // https: {
        //     key: "C:/laragon/etc/ssl/laragon.key",
        //     cert: "C:/laragon/etc/ssl/laragon.crt",
        // },
        // watch: {
        //     ignored: ["public/storage/**/*", "storage/**/*", "**/.env"],
        //     usePolling: true,
        // },
        https: false,
    },
});
