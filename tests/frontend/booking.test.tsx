import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { BookingSection } from "@/components/BookingSection";

describe("BookingSection", () => {
  it("preselects a service supplied by the page", () => {
    render(<BookingSection selectedServiceId="balayage" bookingEndpoint="/reservas" csrfToken="test-token" />);
    expect(screen.getByLabelText("Servicio")).toHaveValue("balayage");
  });

  it("preselects a professional supplied by the team section", () => {
    render(<BookingSection selectedServiceId="" selectedProfessionalId="marta" bookingEndpoint="/reservas" csrfToken="test-token" />);
    expect(screen.getByLabelText("Profesional")).toHaveValue("marta");
  });

  it("keeps the desktop form fields in compact two-column rows", () => {
    render(<BookingSection selectedServiceId="" bookingEndpoint="/reservas" csrfToken="test-token" />);

    expect(screen.getByLabelText("Nombre completo").closest("label")).not.toHaveClass("sm:col-span-2");
    expect(screen.getByLabelText("Teléfono").closest("label")).not.toHaveClass("sm:col-span-2");
    expect(screen.getByLabelText("Servicio").closest(".relative")?.parentElement).not.toHaveClass("sm:col-span-2");
    expect(screen.getByLabelText("Profesional").closest(".relative")?.parentElement).not.toHaveClass("sm:col-span-2");
  });

  it("shows inline errors for an empty submission", () => {
    render(<BookingSection selectedServiceId="" bookingEndpoint="/reservas" csrfToken="test-token" />);
    fireEvent.click(screen.getByRole("button", { name: /confirmar reserva/i }));
    expect(screen.getByText("Escribe tu nombre completo.")).toBeInTheDocument();
    expect(screen.getByText("Selecciona un servicio.")).toBeInTheDocument();
    expect(screen.getByText("Selecciona un profesional o la primera disponibilidad.")).toBeInTheDocument();
  });
});
