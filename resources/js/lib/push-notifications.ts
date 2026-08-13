import { getPwaRegistration } from "@/lib/pwa";

export type PushNotificationState = "checking" | "unavailable" | "idle" | "enabled" | "denied" | "loading" | "error";

export interface PushNotificationConfig {
  publicKey: string;
  subscribeEndpoint: string;
  csrfToken: string;
}

function decodePublicKey(value: string) {
  const padding = "=".repeat((4 - (value.length % 4)) % 4);
  const decoded = window.atob((value + padding).replace(/-/g, "+").replace(/_/g, "/"));
  return Uint8Array.from(decoded, (character) => character.charCodeAt(0));
}

function sameApplicationServerKey(subscription: PushSubscription, publicKey: Uint8Array) {
  const existing = subscription.options.applicationServerKey;
  if (!existing) return false;
  const bytes = new Uint8Array(existing);
  return bytes.length === publicKey.length && bytes.every((value, index) => value === publicKey[index]);
}

function browserSupportsPush() {
  return window.isSecureContext
    && "Notification" in window
    && "serviceWorker" in navigator
    && "PushManager" in window;
}

async function sendSubscription(config: PushNotificationConfig, subscription: PushSubscription, method: "POST" | "DELETE") {
  const json = subscription.toJSON();
  const body = method === "POST"
    ? {
        endpoint: subscription.endpoint,
        keys: json.keys,
        contentEncoding: PushManager.supportedContentEncodings?.[0] ?? "aes128gcm",
      }
    : { endpoint: subscription.endpoint };

  const response = await fetch(config.subscribeEndpoint, {
    method,
    credentials: "same-origin",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-CSRF-TOKEN": config.csrfToken,
    },
    body: JSON.stringify(body),
  });

  if (!response.ok) {
    const payload = await response.json().catch(() => null) as { message?: string } | null;
    throw new Error(payload?.message ?? "No se pudo guardar la configuración de avisos.");
  }
}

export async function inspectPushNotifications(config: PushNotificationConfig): Promise<PushNotificationState> {
  if (!config.publicKey || !browserSupportsPush()) return "unavailable";
  if (Notification.permission === "denied") return "denied";

  const registration = await getPwaRegistration();
  const subscription = await registration?.pushManager.getSubscription();
  if (!subscription) return "idle";

  await sendSubscription(config, subscription, "POST");
  return "enabled";
}

export async function enablePushNotifications(config: PushNotificationConfig) {
  if (!browserSupportsPush() || !config.publicKey) throw new Error("Este dispositivo no admite notificaciones push.");

  const permission = await Notification.requestPermission();
  if (permission !== "granted") throw new Error("Debes permitir las notificaciones en los ajustes del dispositivo.");

  const registration = await getPwaRegistration();
  if (!registration) throw new Error("No se pudo iniciar el servicio de notificaciones.");

  const applicationServerKey = decodePublicKey(config.publicKey);
  let subscription = await registration.pushManager.getSubscription();
  if (subscription && !sameApplicationServerKey(subscription, applicationServerKey)) {
    await subscription.unsubscribe();
    subscription = null;
  }

  subscription ??= await registration.pushManager.subscribe({
    userVisibleOnly: true,
    applicationServerKey,
  });
  await sendSubscription(config, subscription, "POST");
}

export async function disablePushNotifications(config: PushNotificationConfig) {
  const registration = await getPwaRegistration();
  const subscription = await registration?.pushManager.getSubscription();
  if (!subscription) return;

  await sendSubscription(config, subscription, "DELETE");
  await subscription.unsubscribe();
}
