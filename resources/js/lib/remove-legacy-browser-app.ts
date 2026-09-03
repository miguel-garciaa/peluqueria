export async function removeLegacyBrowserApp() {
  const tasks: Promise<unknown>[] = [];

  if ("serviceWorker" in navigator) {
    tasks.push(
      navigator.serviceWorker
        .getRegistrations()
        .then((registrations) => Promise.all(registrations.map((registration) => registration.unregister()))),
    );
  }

  if ("caches" in window) {
    tasks.push(
      window.caches.keys().then((cacheNames) => Promise.all(
        cacheNames
          .filter((cacheName) => cacheName.startsWith("peluqueria-"))
          .map((cacheName) => window.caches.delete(cacheName)),
      )),
    );
  }

  await Promise.allSettled(tasks);
}
