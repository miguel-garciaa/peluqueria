import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vitest/config";

export default defineConfig({
  resolve: {
    alias: { "@": fileURLToPath(new URL("./resources/js", import.meta.url)) },
  },
  test: {
    environment: "jsdom",
    setupFiles: "./tests/frontend/setup.ts",
    css: true,
    coverage: {
      provider: "v8",
      include: ["resources/js/**/*.{ts,tsx}"],
      exclude: [
        "resources/js/main.tsx",
        "resources/js/appointments.tsx",
        "resources/js/vite-env.d.ts",
        "resources/js/types/**",
        "resources/js/data/**",
      ],
      reporter: ["text", "json-summary"],
      thresholds: {
        statements: 82,
        branches: 77,
        functions: 80,
        lines: 88,
      },
    },
  },
});
