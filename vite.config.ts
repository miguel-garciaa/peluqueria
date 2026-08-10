import { fileURLToPath, URL } from "node:url";
import tailwindcss from "@tailwindcss/vite";
import react from "@vitejs/plugin-react";
import laravel from "laravel-vite-plugin";
import { defineConfig } from "vite";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/js/main.tsx"],
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
