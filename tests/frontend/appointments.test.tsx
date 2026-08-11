import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { MyAppointmentsPage } from "@/MyAppointmentsPage";
import type { BookingCatalog, CurrentUser, UserAppointment } from "@/types";

const user: CurrentUser = { name: "Ana López", email: "ana@example.com", phone: "600 123 456", avatarUrl: null };
const catalog: BookingCatalog = {
  services: [{ id: "cut", name: "Corte", durationMinutes: 45, priceFrom: 25, isCustom: false }],
  professionals: [{ id: "marta", name: "Marta Soler", role: "Estilista" }],
};
const appointment: UserAppointment = {
  reference: "01KREFERENCE",
  service: "Corte & Peinado",
  professional: "Marta Soler",
  customDetails: null,
  startsAt: "2026-09-14T08:30:00.000Z",
  endsAt: "2026-09-14T09:30:00.000Z",
  status: "confirmed",
  canCancel: true,
  cancelUrl: "/mis-citas/01KREFERENCE/anular",
};

const renderPage = (items: UserAppointment[] = [appointment]) => render(
  <MyAppointmentsPage
    currentUser={user}
    appointments={items}
    bookingCatalog={catalog}
    bookingEndpoint="/reservas"
    availabilityEndpoint="/reservas/disponibilidad"
    csrfToken="csrf-test"
    flash={null}
  />,
);

describe("MyAppointmentsPage", () => {
  it("shows a single booking action and opens the form without navigating home", () => {
    renderPage();

    expect(screen.queryByRole("button", { name: "Reservar cita" })).not.toBeInTheDocument();
    expect(screen.queryByRole("link", { name: "Reservar cita" })).not.toBeInTheDocument();
    expect(screen.queryByRole("link", { name: "Nueva cita" })).not.toBeInTheDocument();

    fireEvent.click(screen.getByRole("button", { name: "Nueva cita" }));

    expect(screen.getByRole("dialog", { name: "Tu próxima cita" })).toBeInTheDocument();
  });

  it("asks for inline confirmation before submitting a cancellation", () => {
    renderPage();

    fireEvent.click(screen.getByRole("button", { name: "Anular cita" }));

    expect(screen.getByText("¿Seguro que quieres anular esta cita?")).toBeInTheDocument();
    const submit = screen.getByRole("button", { name: "Sí, anular" });
    expect(submit.closest("form")).toHaveAttribute("action", appointment.cancelUrl);
    expect(submit.closest("form")?.querySelector('input[name="_token"]')).toHaveValue("csrf-test");
    expect(submit.closest("form")?.querySelector('input[name="_method"]')).toHaveValue("PATCH");
  });

  it("does not offer cancellation for a non-cancellable appointment", () => {
    renderPage([{ ...appointment, status: "cancelled", canCancel: false }]);

    expect(screen.queryByRole("button", { name: "Anular cita" })).not.toBeInTheDocument();
  });
});
