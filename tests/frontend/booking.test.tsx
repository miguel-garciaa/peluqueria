import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { BookingSection } from "@/components/BookingSection";
import { BookingModal } from "@/components/booking/BookingModal";
import type { BookingCatalog, CurrentUser } from "@/types";

const user: CurrentUser = { name: "Ana López", email: "ana@example.com", phone: "600 123 456", avatarUrl: null };
const catalog: BookingCatalog = {
  services: [
    { id: "cut", name: "Corte & Peinado", durationMinutes: 45, priceFrom: 35, isCustom: false },
    { id: "custom", name: "Personalizado", durationMinutes: 60, priceFrom: null, isCustom: true },
  ],
  professionals: [{ id: "marta", name: "Marta Soler", role: "Especialista en color" }],
};

describe("BookingSection", () => {
  it("opens the floating booking flow from its primary action", () => {
    const onBook = vi.fn();
    render(<BookingSection onBook={onBook} />);
    fireEvent.click(screen.getByRole("button", { name: "Reservar cita" }));
    expect(onBook).toHaveBeenCalledOnce();
  });
});

describe("BookingModal", () => {
  it("shows the summary and waits for explicit confirmation before creating the appointment", async () => {
    const fetchMock = vi.fn()
      .mockResolvedValueOnce({
        ok: true,
        status: 200,
        json: async () => ({ slots: [{ time: "10:30", period: "morning", professional: { slug: "marta", name: "Marta Soler" } }] }),
      })
      .mockResolvedValueOnce({
        ok: true,
        status: 201,
        json: async () => ({ message: "Cita confirmada.", appointment: { reference: "01KTEST", professional: "Marta Soler" } }),
      });
    vi.stubGlobal("fetch", fetchMock);

    render(<BookingModal open onClose={() => undefined} currentUser={user} catalog={catalog} intent={{ serviceId: "cut", professionalId: "marta" }} bookingEndpoint="/reservas" availabilityEndpoint="/reservas/disponibilidad" csrfToken="test" />);
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));

    const calendar = screen.getByLabelText("Calendario de citas");
    const availableDate = Array.from(calendar.querySelectorAll<HTMLButtonElement>('button[aria-pressed]')).find((button) => !button.disabled);
    expect(availableDate).toBeDefined();
    fireEvent.click(availableDate!);
    fireEvent.click(await screen.findByRole("button", { name: "10:30" }));

    fireEvent.submit(screen.getByRole("dialog", { name: "Tu próxima cita" }).querySelector("form")!);
    expect(fetchMock).toHaveBeenCalledTimes(1);

    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));

    expect(screen.getByRole("heading", { name: "Confirma tu cita" })).toBeInTheDocument();
    expect(screen.getByRole("button", { name: "Confirmar cita" })).toBeInTheDocument();
    expect(fetchMock).toHaveBeenCalledTimes(1);

    fireEvent.click(screen.getByRole("button", { name: "Confirmar cita" }));

    expect(await screen.findByRole("heading", { name: "Nos vemos pronto." })).toBeInTheDocument();
    expect(fetchMock).toHaveBeenCalledTimes(2);
    expect(fetchMock.mock.calls[1]?.[1]).toMatchObject({ method: "POST" });

    vi.unstubAllGlobals();
  });

  it("provides safe-area regions for the full-screen mobile layout", () => {
    render(<BookingModal open onClose={() => undefined} currentUser={user} catalog={catalog} intent={{}} bookingEndpoint="/reservas" availabilityEndpoint="/reservas/disponibilidad" csrfToken="test" />);

    const dialog = screen.getByRole("dialog", { name: "Tu próxima cita" });
    expect(dialog.querySelector("header")).toHaveClass("booking-dialog-header");
    expect(dialog.querySelector("footer")).toHaveClass("booking-dialog-footer");
  });

  it("prefills an intent and separates morning from afternoon slots", () => {
    render(<BookingModal open onClose={() => undefined} currentUser={user} catalog={catalog} intent={{ serviceId: "cut", professionalId: "marta" }} bookingEndpoint="/reservas" availabilityEndpoint="/reservas/disponibilidad" csrfToken="test" />);
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    expect(screen.getByRole("button", { name: /Corte & Peinado/ })).toHaveAttribute("aria-pressed", "true");
    expect(screen.getByRole("button", { name: "Marta" })).toHaveAttribute("aria-pressed", "true");
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    expect(screen.getByRole("tab", { name: "Mañana" })).toBeInTheDocument();
    expect(screen.getByRole("tab", { name: "Tarde" })).toBeInTheDocument();
  });

  it("enforces the one-hundred-character custom service limit", () => {
    render(<BookingModal open onClose={() => undefined} currentUser={user} catalog={catalog} intent={{}} bookingEndpoint="/reservas" availabilityEndpoint="/reservas/disponibilidad" csrfToken="test" />);
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    fireEvent.click(screen.getByRole("button", { name: /Personalizado/ }));
    const customDetails = screen.getByRole("textbox", { name: "Cuéntanos qué necesitas" });
    expect(customDetails).toHaveAttribute("maxlength", "100");
    expect(screen.getByText("0/100 caracteres")).toBeInTheDocument();
    fireEvent.change(customDetails, { target: { value: "a".repeat(101) } });
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    expect(screen.getByText("Utiliza un máximo de 100 caracteres.")).toBeInTheDocument();
  });
});
