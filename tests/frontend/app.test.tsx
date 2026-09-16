import { act, fireEvent, render, screen, within } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import App from "@/App";
import type { BookingCatalog, CurrentUser } from "@/types";

const user: CurrentUser = { name: "Ana López", email: "ana@example.com", phone: "600123456", avatarUrl: null };
const catalog: BookingCatalog = {
  services: [{ id: "cut", name: "Corte", description: "Corte personalizado", durationMinutes: 45, priceFrom: 25, isCustom: false }],
  professionals: [{ id: "marta", name: "Marta Soler", role: "Estilista", serviceIds: ["cut"] }],
  bizumEnabled: false,
};

const renderApp = (currentUser: CurrentUser | null, authMessage: string | null = null, authMessageType: "success" | "error" = "success") => render(
  <App
    bookingEndpoint="/reservas"
    availabilityEndpoint="/reservas/disponibilidad"
    bookingCatalog={catalog}
    csrfToken="csrf"
    currentUser={currentUser}
    authMessage={authMessage}
    authMessageType={authMessageType}
  />,
);

describe("App", () => {
  it("asks guests to authenticate and lets them dismiss the notice", () => {
    renderApp(null);
    fireEvent.click(screen.getAllByRole("button", { name: "Reservar cita" })[0]);

    const alert = screen.getByRole("alert");
    expect(alert).toHaveTextContent("Inicia sesión para reservar");
    expect(within(alert).getByRole("link", { name: "Iniciar sesión" })).toHaveAttribute("href", "/login");
    fireEvent.click(screen.getByRole("button", { name: "Cerrar aviso" }));
    expect(screen.queryByText("Inicia sesión para reservar")).not.toBeInTheDocument();
  });

  it("automatically removes the guest authentication notice", () => {
    vi.useFakeTimers();
    try {
      renderApp(null);
      fireEvent.click(screen.getAllByRole("button", { name: "Reservar cita" })[0]);
      act(() => vi.advanceTimersByTime(3000));
      expect(screen.queryByText("Inicia sesión para reservar")).not.toBeInTheDocument();
    } finally {
      vi.useRealTimers();
    }
  });

  it("opens booking for authenticated users and carries a professional intent", () => {
    renderApp(user, "Sesión iniciada");
    expect(screen.getByRole("status")).toHaveTextContent("Sesión iniciada");

    fireEvent.click(screen.getByRole("button", { name: "Reservar con Marta" }));
    expect(screen.getByRole("dialog", { name: "Tu próxima cita" })).toBeInTheDocument();
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    expect(screen.getByRole("button", { name: "Marta" })).toHaveAttribute("aria-pressed", "true");
    fireEvent.click(screen.getByRole("button", { name: "Cerrar reserva" }));
    expect(screen.queryByRole("dialog", { name: "Tu próxima cita" })).not.toBeInTheDocument();
  });
});
