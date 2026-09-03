import { fileURLToPath, URL } from "node:url";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

export default defineConfig({
  build: {
    // Cloudflare or a browser can still reference a previous hashed bundle while
    // a deployment is propagating. Keep those immutable assets available.
    emptyOutDir: false,
  },
  plugins: [
    laravel({
      input: [
        "resources/js/main.tsx",
        "resources/js/appointments.tsx",
        "resources/css/filament/admin/theme.css",
      ],
      refresh: ["resources/views/**", "app/Http/**", "routes/**"],
    }),
    react(),
    tailwindcss(),
  ],
  resolve: {
    alias: { "@": fileURLToPath(new URL("./resources/js", import.meta.url)) },
  },
  server: {
    host: "0.0.0.0",
    watch: { ignored: ["**/storage/framework/views/**"] },
  },
});
