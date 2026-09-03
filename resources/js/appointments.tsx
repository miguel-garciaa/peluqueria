import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { MyAppointmentsPage } from "@/MyAppointmentsPage";
import { removeLegacyBrowserApp } from "@/lib/remove-legacy-browser-app";
import type { BookingCatalog, CurrentUser, UserAppointment } from "@/types";
import "../css/app.css";

void removeLegacyBrowserApp();

const root = document.getElementById("appointments-root");
if (!root) throw new Error("No se encontró el contenedor de citas.");

const currentUser = JSON.parse(root.dataset.currentUser || "null") as CurrentUser | null;
const appointments = JSON.parse(root.dataset.appointments || "[]") as UserAppointment[];
const bookingCatalog = JSON.parse(root.dataset.bookingCatalog || '{"services":[],"professionals":[]}') as BookingCatalog;
const bookingEndpoint = root.dataset.bookingEndpoint ?? "/reservas";
const availabilityEndpoint = root.dataset.availabilityEndpoint ?? "/reservas/disponibilidad";
const flash = JSON.parse(root.dataset.flash || "null") as { message: string | null; type: "success" | "error" } | null;
const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? "";
if (!currentUser) throw new Error("La vista de citas requiere una sesión activa.");

createRoot(root).render(<StrictMode><MyAppointmentsPage currentUser={currentUser} appointments={appointments} bookingCatalog={bookingCatalog} bookingEndpoint={bookingEndpoint} availabilityEndpoint={availabilityEndpoint} csrfToken={csrfToken} flash={flash} /></StrictMode>);
