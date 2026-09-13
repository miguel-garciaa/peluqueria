import { describe, expect, it, vi } from "vitest";
import { removeLegacyBrowserApp } from "@/lib/remove-legacy-browser-app";
import { cn } from "@/lib/utils";

describe("removeLegacyBrowserApp", () => {
  it("unregisters workers and removes only application-owned caches", async () => {
    const unregisterA = vi.fn().mockResolvedValue(true);
    const unregisterB = vi.fn().mockRejectedValue(new Error("already gone"));
    Object.defineProperty(navigator, "serviceWorker", {
      configurable: true,
      value: { getRegistrations: vi.fn().mockResolvedValue([{ unregister: unregisterA }, { unregister: unregisterB }]) },
    });
    const deleteCache = vi.fn().mockResolvedValue(true);
    Object.defineProperty(window, "caches", {
      configurable: true,
      value: { keys: vi.fn().mockResolvedValue(["peluqueria-v1", "other-site", "peluqueria-images"]), delete: deleteCache },
    });

    await expect(removeLegacyBrowserApp()).resolves.toBeUndefined();
    expect(unregisterA).toHaveBeenCalledOnce();
    expect(unregisterB).toHaveBeenCalledOnce();
    expect(deleteCache).toHaveBeenCalledTimes(2);
    expect(deleteCache).toHaveBeenCalledWith("peluqueria-v1");
    expect(deleteCache).not.toHaveBeenCalledWith("other-site");
  });

  it("does nothing on browsers without service workers or CacheStorage", async () => {
    Reflect.deleteProperty(navigator, "serviceWorker");
    Reflect.deleteProperty(window, "caches");

    await expect(removeLegacyBrowserApp()).resolves.toBeUndefined();
  });
});

describe("cn", () => {
  it("combines conditional classes and resolves Tailwind conflicts", () => {
    const shouldHide = new URLSearchParams().has("hidden");
    expect(cn("px-2", shouldHide && "hidden", ["font-bold", { block: true }], "px-6")).toBe("font-bold block px-6");
  });
});
