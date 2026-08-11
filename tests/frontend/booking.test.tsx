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

  it("enforces the forty-word custom service limit", () => {
    render(<BookingModal open onClose={() => undefined} currentUser={user} catalog={catalog} intent={{}} bookingEndpoint="/reservas" availabilityEndpoint="/reservas/disponibilidad" csrfToken="test" />);
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    fireEvent.click(screen.getByRole("button", { name: /Personalizado/ }));
    fireEvent.change(screen.getByRole("textbox", { name: "Cuéntanos qué necesitas" }), { target: { value: Array.from({ length: 41 }, () => "detalle").join(" ") } });
    fireEvent.click(screen.getByRole("button", { name: "Continuar" }));
    expect(screen.getByText("Utiliza un máximo de 40 palabras.")).toBeInTheDocument();
  });
});
