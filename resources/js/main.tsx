import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import App from "./App";
import { getHeroMotionMode, type HeroNavigationType } from "@/lib/hero-motion";
import { isMobileView, mobileViewFromPath } from "@/lib/mobile-navigation";
import { registerPwa } from "@/lib/pwa";
import "../css/app.css";

const navigationEntry = performance.getEntriesByType("navigation")[0] as PerformanceNavigationTiming | undefined;
const navigationType = (navigationEntry?.type ?? "navigate") as HeroNavigationType;
const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const documentElement = document.documentElement;

registerPwa();

documentElement.dataset.heroMotion = getHeroMotionMode(navigationType, reducedMotion);
if (documentElement.dataset.heroMotion === "play") {
  window.setTimeout(() => { documentElement.dataset.heroMotion = "settled"; }, 1400);
}

const appRoot = document.getElementById("root");
if (!appRoot) throw new Error("No se encontró el contenedor principal de la aplicación.");

const bookingEndpoint = appRoot.dataset.bookingEndpoint ?? "/reservas";
const availabilityEndpoint = appRoot.dataset.availabilityEndpoint ?? "/reservas/disponibilidad";
const bookingCatalog = JSON.parse(appRoot.dataset.bookingCatalog || '{"services":[],"professionals":[]}');
const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? "";
const currentUser = JSON.parse(appRoot.dataset.currentUser || "null");
const initialMobileView = isMobileView(appRoot.dataset.mobileView)
  ? appRoot.dataset.mobileView
  : mobileViewFromPath(window.location.pathname);
const authMessage = appRoot.dataset.authMessage || null;
const authMessageType = appRoot.dataset.authMessageType === "error" ? "error" : "success";
const pushPublicKey = appRoot.dataset.pushPublicKey ?? "";
const pushSubscriptionEndpoint = appRoot.dataset.pushSubscriptionEndpoint ?? "/notificaciones/suscripcion";

createRoot(appRoot).render(
  <StrictMode>
    <App bookingEndpoint={bookingEndpoint} availabilityEndpoint={availabilityEndpoint} bookingCatalog={bookingCatalog} csrfToken={csrfToken} currentUser={currentUser} initialMobileView={initialMobileView} authMessage={authMessage} authMessageType={authMessageType} pushPublicKey={pushPublicKey} pushSubscriptionEndpoint={pushSubscriptionEndpoint} />
  </StrictMode>,
);
