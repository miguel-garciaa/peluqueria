import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { inspectPushNotifications } from "@/lib/push-notifications";

const config = {
  publicKey: "AQID",
  subscribeEndpoint: "/notificaciones/suscripcion",
  csrfToken: "csrf-test",
};

describe("push notifications", () => {
  beforeEach(() => {
    Object.defineProperty(window, "isSecureContext", { configurable: true, value: true });
    Object.defineProperty(window, "Notification", {
      configurable: true,
      value: { permission: "granted", requestPermission: vi.fn().mockResolvedValue("granted") },
    });
    Object.defineProperty(window, "PushManager", {
      configurable: true,
      value: { supportedContentEncodings: ["aes128gcm"] },
    });
  });

  afterEach(() => {
    vi.restoreAllMocks();
    vi.unstubAllGlobals();
  });

  it("removes a subscription created with an obsolete VAPID key", async () => {
    const unsubscribe = vi.fn().mockResolvedValue(true);
    const subscription = {
      endpoint: "https://web.push.apple.com/device",
      options: { applicationServerKey: new Uint8Array([9, 9, 9]).buffer },
      toJSON: () => ({ keys: { p256dh: "key", auth: "auth" } }),
      unsubscribe,
    } as unknown as PushSubscription;
    const getRegistration = vi.fn().mockResolvedValue({
      pushManager: { getSubscription: vi.fn().mockResolvedValue(subscription) },
    });
    Object.defineProperty(navigator, "serviceWorker", {
      configurable: true,
      value: { getRegistration, register: vi.fn(), addEventListener: vi.fn() },
    });
    const fetchMock = vi.fn().mockResolvedValue({ ok: true });
    vi.stubGlobal("fetch", fetchMock);

    await expect(inspectPushNotifications(config)).resolves.toBe("idle");
    expect(fetchMock).toHaveBeenCalledWith(config.subscribeEndpoint, expect.objectContaining({ method: "DELETE" }));
    expect(unsubscribe).toHaveBeenCalledOnce();
  });
});
