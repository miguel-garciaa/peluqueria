import type { MobileView } from "@/types";

export const mobileViewPaths: Record<MobileView, string> = {
  inicio: "/",
  servicios: "/servicios",
  equipo: "/equipo",
  reservar: "/reservar",
  galeria: "/galeria",
  cuenta: "/cuenta",
};

export const mobileViewTitles: Record<MobileView, string> = {
  inicio: "Peluquería y barbería — Tu ciudad",
  servicios: "Servicios — Peluquería",
  equipo: "Equipo — Peluquería",
  reservar: "Reservar cita — Peluquería",
  galeria: "Galería — Peluquería",
  cuenta: "Cuenta — Peluquería",
};

export function isMobileView(value: string | undefined): value is MobileView {
  return Boolean(value && value in mobileViewPaths);
}

export function mobileViewFromPath(pathname: string): MobileView {
  const normalizedPath = pathname.length > 1 ? pathname.replace(/\/$/, "") : pathname;
  return (Object.entries(mobileViewPaths).find(([, path]) => path === normalizedPath)?.[0] as MobileView | undefined) ?? "inicio";
}
