import "@fontsource-variable/bricolage-grotesque";
import "@fontsource-variable/manrope";
import { StrictMode } from "react";
import { createRoot } from "react-dom/client";
import { MyAppointmentsPage } from "@/MyAppointmentsPage";
import type { CurrentUser, UserAppointment } from "@/types";
import "../css/app.css";

const root = document.getElementById("appointments-root");
if (!root) throw new Error("No se encontró el contenedor de citas.");

const currentUser = JSON.parse(root.dataset.currentUser || "null") as CurrentUser | null;
const appointments = JSON.parse(root.dataset.appointments || "[]") as UserAppointment[];
const csrfToken = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? "";
if (!currentUser) throw new Error("La vista de citas requiere una sesión activa.");

createRoot(root).render(<StrictMode><MyAppointmentsPage currentUser={currentUser} appointments={appointments} csrfToken={csrfToken} /></StrictMode>);
