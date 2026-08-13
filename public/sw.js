const CACHE_VERSION = "peluqueria-v4";
const PRECACHE = `${CACHE_VERSION}-precache`;
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const IMAGE_CACHE = `${CACHE_VERSION}-images`;
const META_CACHE = `${CACHE_VERSION}-meta`;
const OFFLINE_URL = "/offline.html";
const IMAGE_MAX_ENTRIES = 40;
const IMAGE_MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000;

const PRIVATE_PATHS = [
  /^\/auth(?:\/|$)/,
  /^\/login(?:\/|$)/,
  /^\/logout(?:\/|$)/,
  /^\/reservas(?:\/|$)/,
  /^\/mis-citas(?:\/|$)/,
  /^\/admin(?:\/|$)/,
  /^\/filament(?:\/|$)/,
  /^\/livewire(?:\/|$)/,
  /^\/sanctum(?:\/|$)/,
];

const PRECACHE_URLS = [
  OFFLINE_URL,
  "/favicon.svg",
  "/icons/icon-192.png",
  "/icons/icon-512.png",
  "/icons/icon-maskable-512.png",
];

self.addEventListener("install", (event) => {
  event.waitUntil(caches.open(PRECACHE).then((cache) => cache.addAll(PRECACHE_URLS)));
});

self.addEventListener("activate", (event) => {
  const currentCaches = new Set([PRECACHE, STATIC_CACHE, IMAGE_CACHE, META_CACHE]);
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(keys.filter((key) => key.startsWith("peluqueria-") && !currentCaches.has(key)).map((key) => caches.delete(key))))
      .then(() => self.clients.claim()),
  );
});

self.addEventListener("message", (event) => {
  if (event.data?.type === "SKIP_WAITING") self.skipWaiting();
});

self.addEventListener("push", (event) => {
  if (!event.data) return;

  let payload;
  try {
    payload = event.data.json();
  } catch {
    payload = { title: "Peluquería", body: event.data.text() };
  }

  const { title = "Peluquería", ...options } = payload;
  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener("notificationclick", (event) => {
  event.notification.close();
  const targetUrl = new URL(event.notification.data?.url || "/", self.location.origin).href;

  event.waitUntil(
    self.clients.matchAll({ type: "window", includeUncontrolled: true }).then(async (clients) => {
      const sameOriginClient = clients.find((client) => new URL(client.url).origin === self.location.origin);
      if (sameOriginClient) {
        await sameOriginClient.navigate(targetUrl);
        return sameOriginClient.focus();
      }

      return self.clients.openWindow(targetUrl);
    }),
  );
});

function isPrivateRequest(url) {
  return url.origin === self.location.origin && PRIVATE_PATHS.some((pattern) => pattern.test(url.pathname));
}

function metadataRequest(url) {
  return new Request(`${self.location.origin}/__pwa-cache-meta__?url=${encodeURIComponent(url)}`);
}

async function recordCachedAt(url) {
  const cache = await caches.open(META_CACHE);
  await cache.put(metadataRequest(url), new Response(String(Date.now())));
}

async function isExpired(url) {
  const cache = await caches.open(META_CACHE);
  const response = await cache.match(metadataRequest(url));
  if (!response) return true;
  const cachedAt = Number(await response.text());
  return !Number.isFinite(cachedAt) || Date.now() - cachedAt > IMAGE_MAX_AGE_MS;
}

async function trimImageCache() {
  const cache = await caches.open(IMAGE_CACHE);
  const keys = await cache.keys();
  const excess = keys.length - IMAGE_MAX_ENTRIES;
  if (excess <= 0) return;

  const meta = await caches.open(META_CACHE);
  await Promise.all(keys.slice(0, excess).map(async (request) => {
    await cache.delete(request);
    await meta.delete(metadataRequest(request.url));
  }));
}

async function cacheFirst(request) {
  const cache = await caches.open(STATIC_CACHE);
  const cached = await cache.match(request);
  if (cached) return cached;

  const response = await fetch(request);
  if (response.ok) await cache.put(request, response.clone());
  return response;
}

async function staleWhileRevalidateImage(request) {
  const cache = await caches.open(IMAGE_CACHE);
  let cached = await cache.match(request);
  if (cached && await isExpired(request.url)) {
    await cache.delete(request);
    cached = undefined;
  }

  const networkResponse = fetch(request).then(async (response) => {
    if (response.ok || response.type === "opaque") {
      await cache.put(request, response.clone());
      await recordCachedAt(request.url);
      await trimImageCache();
    }
    return response;
  });

  return cached || networkResponse;
}

self.addEventListener("fetch", (event) => {
  const { request } = event;
  const url = new URL(request.url);

  if (request.method !== "GET" || isPrivateRequest(url)) {
    event.respondWith(fetch(request));
    return;
  }

  if (request.mode === "navigate") {
    event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
    return;
  }

  if (url.origin === self.location.origin && url.pathname.startsWith("/build/")) {
    event.respondWith(cacheFirst(request));
    return;
  }

  const isPublicImageHost = url.origin === self.location.origin || url.hostname === "images.unsplash.com";
  if (request.destination === "image" && isPublicImageHost) {
    event.respondWith(staleWhileRevalidateImage(request));
  }
});
