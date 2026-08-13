export const PWA_UPDATE_AVAILABLE_EVENT = "pwa:update-available";

let refreshRequested = false;
let registrationStarted = false;

function watchRegistration(registration: ServiceWorkerRegistration) {
  if (registration.waiting && navigator.serviceWorker.controller) {
    window.dispatchEvent(new Event(PWA_UPDATE_AVAILABLE_EVENT));
  }

  registration.addEventListener("updatefound", () => {
    const installingWorker = registration.installing;
    if (!installingWorker) return;

    installingWorker.addEventListener("statechange", () => {
      if (installingWorker.state === "installed" && navigator.serviceWorker.controller) {
        window.dispatchEvent(new Event(PWA_UPDATE_AVAILABLE_EVENT));
      }
    });
  });
}

export function registerPwa() {
  if (registrationStarted || !("serviceWorker" in navigator)) return;
  registrationStarted = true;

  window.addEventListener("load", async () => {
    try {
      const registration = await navigator.serviceWorker.register("/sw.js", {
        scope: "/",
        updateViaCache: "none",
      });
      watchRegistration(registration);
      void registration.update();
    } catch (error) {
      console.warn("No se pudo registrar el modo instalable.", error);
    }
  }, { once: true });

  navigator.serviceWorker.addEventListener("controllerchange", () => {
    if (!refreshRequested) return;
    window.location.reload();
  });
}

export async function applyPwaUpdate() {
  if (!("serviceWorker" in navigator)) return false;
  const registration = await navigator.serviceWorker.getRegistration("/");
  if (!registration?.waiting) return false;

  refreshRequested = true;
  registration.waiting.postMessage({ type: "SKIP_WAITING" });
  return true;
}
