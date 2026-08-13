import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import App from "@/App";
import type { BookingCatalog, CurrentUser } from "@/types";

const user: CurrentUser = {
  name: "Miguel García",
  email: "miguel@example.com",
  phone: "600 00 00 00",
  avatarUrl: null,
};

const catalog: BookingCatalog = {
  services: [{ id: "corte", name: "Corte", description: "Corte personalizado", durationMinutes: 45, priceFrom: 25, isCustom: false }],
  professionals: [{ id: "ana", name: "Ana", role: "Estilista", serviceIds: ["corte"] }],
};

const props = {
  bookingEndpoint: "/reservas",
  availabilityEndpoint: "/reservas/disponibilidad",
  bookingCatalog: catalog,
  csrfToken: "csrf-test",
  currentUser: user,
  authMessage: null,
  authMessageType: "success" as const,
};

describe("mobile app booking navigation", () => {
  it("opens the booking form immediately from the bottom navigation", () => {
    const matchMedia = vi.spyOn(window, "matchMedia").mockImplementation((query) => ({
      matches: query === "(max-width: 47.999rem)" || query === "(display-mode: standalone)",
      media: query,
      onchange: null,
      addListener: () => {},
      removeListener: () => {},
      addEventListener: () => {},
      removeEventListener: () => {},
      dispatchEvent: () => false,
    }));

    render(<App {...props} initialMobileView="inicio" />);

    fireEvent.click(screen.getByRole("link", { name: "Reservar" }));

    expect(screen.getByRole("dialog", { name: "Tu próxima cita" })).toBeInTheDocument();
    expect(screen.queryByText("Todo listo en cuatro pasos.")).not.toBeInTheDocument();
    expect(window.location.pathname).toBe("/reservar");

    fireEvent.click(screen.getByRole("button", { name: "Cancelar" }));

    expect(screen.queryByRole("dialog", { name: "Tu próxima cita" })).not.toBeInTheDocument();
    expect(window.location.pathname).toBe("/");
    matchMedia.mockRestore();
  });

  it("opens the form immediately when the installed app starts on /reservar", async () => {
    const matchMedia = vi.spyOn(window, "matchMedia").mockImplementation((query) => ({
      matches: query === "(max-width: 47.999rem)" || query === "(display-mode: standalone)",
      media: query,
      onchange: null,
      addListener: () => {},
      removeListener: () => {},
      addEventListener: () => {},
      removeEventListener: () => {},
      dispatchEvent: () => false,
    }));

    render(<App {...props} initialMobileView="reservar" />);

    expect(await screen.findByRole("dialog", { name: "Tu próxima cita" })).toBeInTheDocument();
    expect(screen.queryByText("Todo listo en cuatro pasos.")).not.toBeInTheDocument();
    matchMedia.mockRestore();
  });

  it("keeps the complete website when opened in a mobile browser", () => {
    const matchMedia = vi.spyOn(window, "matchMedia").mockImplementation((query) => ({
      matches: query === "(max-width: 47.999rem)",
      media: query,
      onchange: null,
      addListener: () => {},
      removeListener: () => {},
      addEventListener: () => {},
      removeEventListener: () => {},
      dispatchEvent: () => false,
    }));

    render(<App {...props} initialMobileView="inicio" />);

    expect(screen.queryByRole("navigation", { name: "Navegación móvil" })).not.toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Abrir menú" })).toBeInTheDocument();
    expect(document.querySelector("#servicios")?.parentElement).not.toHaveClass("hidden");
    expect(screen.getByRole("navigation", { name: "Navegación del pie" })).toBeInTheDocument();
    matchMedia.mockRestore();
  });
});
