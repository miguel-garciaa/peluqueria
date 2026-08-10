import { fireEvent, render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { Services } from "@/components/Services";

describe("Services", () => {
  it("opens service details and books the selected service", () => {
    vi.useFakeTimers();
    const onBook = vi.fn();
    render(<Services onBook={onBook} />);

    fireEvent.click(screen.getByRole("button", { name: "Ver detalles de Balayage" }));
    expect(screen.getByRole("dialog")).toBeVisible();
    expect(screen.getByText("Diseño de color personalizado")).toBeInTheDocument();

    fireEvent.click(screen.getByRole("button", { name: "Reservar este servicio" }));
    vi.advanceTimersByTime(180);
    expect(onBook).toHaveBeenCalledWith("balayage");
    vi.useRealTimers();
  });

  it("shows the complete service detail without an internal scroll area", () => {
    render(<Services onBook={vi.fn()} />);

    fireEvent.click(screen.getByRole("button", { name: "Ver detalles de Ritual Capilar" }));

    const dialog = screen.getByRole("dialog");
    expect(dialog.querySelector(".overflow-y-auto")).not.toBeInTheDocument();
  });

  it("keeps the booking action available for every service", () => {
    render(<Services onBook={vi.fn()} />);

    for (const serviceName of ["Corte & Peinado", "Balayage", "Keratina & Brillo", "Barba & Estilo", "Ritual Capilar", "Peinado de Evento"]) {
      fireEvent.click(screen.getByRole("button", { name: `Ver detalles de ${serviceName}` }));
      expect(screen.getByRole("button", { name: "Reservar este servicio" })).toBeVisible();
      fireEvent.click(screen.getByRole("button", { name: "Cerrar detalles del servicio" }));
    }
  });
});
