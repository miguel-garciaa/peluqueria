import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { MyAppointmentsPage } from "@/MyAppointmentsPage";
import type { BookingCatalog, CurrentUser, UserAppointment } from "@/types";
import { registerPwa } from "@/lib/pwa";
import "../css/app.css";

registerPwa();

const root = document.getElementById("appointments-root");
if (!root) throw new Error("No se encontró el contenedor de citas.");

const currentUser = JSON.parse(root.dataset.currentUser || "null") as CurrentUser | null;
const appointments = JSON.parse(root.dataset.appointments || "[]") as UserAppointment[];
const bookingCatalog = JSON.parse(root.dataset.bookingCatalog || '{"services":[],"professionals":[]}') as BookingCatalog;
const bookingEndpoint = root.dataset.bookingEndpoint ?? "/reservas";
const availabilityEndpoint = root.dataset.availabilityEndpoint ?? "/reservas/disponibilidad";
const flash = JSON.parse(root.dataset.flash || "null") as { message: string | null; type: "success" | "error" } | null;
const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? "";
const pushPublicKey = root.dataset.pushPublicKey ?? "";
const pushSubscriptionEndpoint = root.dataset.pushSubscriptionEndpoint ?? "/notificaciones/suscripcion";
if (!currentUser) throw new Error("La vista de citas requiere una sesión activa.");

createRoot(root).render(<StrictMode><MyAppointmentsPage currentUser={currentUser} appointments={appointments} bookingCatalog={bookingCatalog} bookingEndpoint={bookingEndpoint} availabilityEndpoint={availabilityEndpoint} csrfToken={csrfToken} flash={flash} pushPublicKey={pushPublicKey} pushSubscriptionEndpoint={pushSubscriptionEndpoint} /></StrictMode>);
