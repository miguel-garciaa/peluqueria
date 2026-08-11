import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { MyAppointmentsPage } from "@/MyAppointmentsPage";
import type { CurrentUser, UserAppointment } from "@/types";

const user: CurrentUser = { name: "Ana López", email: "ana@example.com", phone: "600 123 456", avatarUrl: null };
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

describe("MyAppointmentsPage", () => {
  it("asks for inline confirmation before submitting a cancellation", () => {
    render(<MyAppointmentsPage currentUser={user} appointments={[appointment]} csrfToken="csrf-test" flash={null} />);

    fireEvent.click(screen.getByRole("button", { name: "Anular cita" }));

    expect(screen.getByText("¿Seguro que quieres anular esta cita?")).toBeInTheDocument();
    const submit = screen.getByRole("button", { name: "Sí, anular" });
    expect(submit.closest("form")).toHaveAttribute("action", appointment.cancelUrl);
    expect(submit.closest("form")?.querySelector('input[name="_token"]')).toHaveValue("csrf-test");
    expect(submit.closest("form")?.querySelector('input[name="_method"]')).toHaveValue("PATCH");
  });

  it("does not offer cancellation for a non-cancellable appointment", () => {
    render(<MyAppointmentsPage currentUser={user} appointments={[{ ...appointment, status: "cancelled", canCancel: false }]} csrfToken="csrf-test" flash={null} />);

    expect(screen.queryByRole("button", { name: "Anular cita" })).not.toBeInTheDocument();
  });
});
