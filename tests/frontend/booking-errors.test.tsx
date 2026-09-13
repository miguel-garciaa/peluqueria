import { fireEvent, render, screen } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import { BookingModal } from "@/components/booking/BookingModal";
import type { BookingCatalog, CurrentUser } from "@/types";

const user: CurrentUser = { name: "Ana López", email: "ana@example.com", phone: "600123456", avatarUrl: null };
const catalog: BookingCatalog = {
  services: [{ id: "cut", name: "Corte", description: "Corte", durationMinutes: 45, priceFrom: 25, isCustom: false }],
  professionals: [{ id: "marta", name: "Marta Soler", role: "Estilista", serviceIds: ["cut"] }],
  bizumEnabled: false,
};

const renderModal = () => render(
  <BookingModal
    open
    onClose={vi.fn()}
    currentUser={user}
    catalog={catalog}
    intent={{ serviceId: "cut", professionalId: "marta" }}
    bookingEndpoint="/reservas"
    availabilityEndpoint="/reservas/disponibilidad"
    csrfToken="csrf-test"
  />,
);

async function reachConfirmation() {
  fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
  fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
  const calendar = screen.getByLabelText("Calendario de citas");
  const date = Array.from(calendar.querySelectorAll<HTMLButtonElement>("button[aria-pressed]"))
    .find((button) => !button.disabled);
  expect(date).toBeDefined();
  fireEvent.click(date!);
  fireEvent.click(await screen.findByRole("button", { name: "10:00" }));
  fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
  expect(screen.getByRole("heading", { name: "Confirma tu cita" })).toBeInTheDocument();
}

afterEach(() => vi.unstubAllGlobals());

describe("BookingModal failure recovery", () => {
  it("shows availability API failures without leaving a loading state", async () => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
      ok: false,
      status: 503,
      json: async () => ({ message: "Agenda temporalmente no disponible" }),
    }));
    renderModal();
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    const calendar = screen.getByLabelText("Calendario de citas");
    const date = Array.from(calendar.querySelectorAll<HTMLButtonElement>("button[aria-pressed]"))
      .find((button) => !button.disabled);
    fireEvent.click(date!);

    expect(await screen.findByText("Agenda temporalmente no disponible")).toBeInTheDocument();
    expect(screen.queryByText("Consultando agenda…")).not.toBeInTheDocument();
  });

  it("routes server validation errors back to the affected step", async () => {
    const fetchMock = vi.fn()
      .mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({ slots: [{ time: "10:00", period: "morning", professional: { slug: "marta", name: "Marta Soler" } }] }) })
      .mockResolvedValueOnce({ ok: false, status: 422, json: async () => ({ errors: { serviceId: ["El servicio acaba de retirarse."] } }) });
    vi.stubGlobal("fetch", fetchMock);
    renderModal();
    await reachConfirmation();
    fireEvent.click(screen.getByRole("button", { name: "Confirmar cita" }));

    expect(await screen.findByText("El servicio acaba de retirarse.")).toBeInTheDocument();
    expect(screen.getByRole("heading", { name: "Servicio y profesional" })).toBeInTheDocument();
  });

  it("keeps the confirmation recoverable after an unexpected server failure", async () => {
    const fetchMock = vi.fn()
      .mockResolvedValueOnce({ ok: true, status: 200, json: async () => ({ slots: [{ time: "10:00", period: "morning", professional: { slug: "marta", name: "Marta Soler" } }] }) })
      .mockResolvedValueOnce({ ok: false, status: 500, json: async () => ({ message: "No se pudo guardar la cita" }) });
    vi.stubGlobal("fetch", fetchMock);
    renderModal();
    await reachConfirmation();
    fireEvent.click(screen.getByRole("button", { name: "Confirmar cita" }));

    expect(await screen.findByText("No se pudo guardar la cita")).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Confirmar cita" })).toBeEnabled();
  });
});
